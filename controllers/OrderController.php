<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\forms\Order\CreateOrderForm;
use app\models\Coupons;
use app\models\CouponUsages;
use app\models\forms\Order\UpdateStatusOrderForm;
use app\models\OrderItems;
use app\models\Orders;
use app\models\Payments;
use app\models\Products;
use app\models\ProductVariants;
use app\models\response\Order\OrderResponse;
use app\models\search\OrderSearch;
use Yii;
use yii\web\NotFoundHttpException;

class OrderController extends BaseController
{
    public $modelClass = 'app\models\Orders';

    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Orders retrieved successfully');
    }

    public function actionView($id)
    {
        $model = OrderResponse::find()
            ->with(['orderItems'])
            ->where(['id' => $id])
            ->one();

        if ($model === null) {
            return $this->json(false, null, 'Order not found', 404);
        }

        return $this->json(true, $model, 'Order retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateOrderForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $order = $this->buildOrderFromForm($form);
            $order->total = 0;

            if ($order->save()) {
                $items = $this->buildItemsFromFields($form);
                if (empty($items)) {
                    $form->addError('item_product_id', 'Order item is required.');
                    $transaction->rollBack();
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $total = $this->createOrderItems($order, $form, $items);
                if ($total === false) {
                    $transaction->rollBack();
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $couponResult = $this->resolveCouponDiscount($form, $total);
                if ($couponResult === false) {
                    $transaction->rollBack();
                    return $this->json(false, $form->errors, 'Validation failed', 422);
                }

                $coupon = $couponResult['coupon'];
                $discountAmount = $couponResult['discount'];
                $order->setAttributes([
                    'discount_amount' => $discountAmount,
                    'is_discounted' => $coupon !== null ? 1 : 0,
                    'total' => $total + (float) $order->shipping_fee - $discountAmount,
                ], false);
                if (!$order->save(false, ['total', 'discount_amount', 'is_discounted'])) {
                    throw new \RuntimeException('Failed to update order total.');
                }

                if ($coupon !== null) {
                    if (!$this->recordCouponUsage($coupon, $order, $discountAmount, $form)) {
                        $transaction->rollBack();
                        return $this->json(false, $form->errors, 'Validation failed', 422);
                    }
                }

                $this->createOrUpdatePayment($order, $form);
                $this->clearCartUser((int) $order->user_id);
                $transaction->commit();
                $responseModel = $this->findModelWithItems($order->id);
                return $this->json(true, $responseModel, 'Order created successfully', 201);
            }

            $this->addOrderErrorsToForm($order, $form);
            $transaction->rollBack();
            return $this->json(false, $form->errors, 'Validation failed', 422);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionUpdateStatusOrder($id = null)
    {
        $form = new UpdateStatusOrderForm();
        $form->load($this->request->bodyParams, '');

        $resolvedId = $id ?? $form->id;
        if ($resolvedId === null) {
            return $this->json(false, null, 'Order id is required', 422);
        }

        $model = OrderResponse::findOne(['id' => $resolvedId]);
        if (!$model) {
            return $this->json(false, null, 'Order not found', 404);
        }
        $form->id = $resolvedId;
        if ($form->status === null || $form->status === '') {
            $form->status = $model->status;
            $form->payment_status = $model->payment_status;
        }

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }
        $model->setAttributes([
            'status' => $form->status,
            'payment_status' => $form->payment_status,
        ], false);
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Order status updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }


    protected function findModel($id)
    {
        if (($model = Orders::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Order not found');
    }

    protected function findModelWithItems($id)
    {
        $model = Orders::find()
            ->with(['orderItems'])
            ->where(['id' => $id])
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Order not found');
    }

    private function createOrderItems(Orders $order, CreateOrderForm $form, array $items)
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            if ($quantity < 1) {
                continue;
            }

            $key = (int) $item['product_id'] . ':' . (int) $item['variant_id'];
            if (!isset($normalizedItems[$key])) {
                $normalizedItems[$key] = [
                    'product_id' => (int) $item['product_id'],
                    'variant_id' => (int) $item['variant_id'],
                    'quantity' => 0,
                ];
            }
            $normalizedItems[$key]['quantity'] += $quantity;
        }

        if (empty($normalizedItems)) {
            $form->addError('item_product_id', 'Order item is required.');
            return false;
        }

        $productIds = array_values(array_unique(array_column($normalizedItems, 'product_id')));
        $variantIds = array_values(array_unique(array_column($normalizedItems, 'variant_id')));

        $products = Products::find()
            ->where(['id' => $productIds])
            ->indexBy('id')
            ->all();
        $variants = ProductVariants::find()
            ->where(['id' => $variantIds])
            ->indexBy('id')
            ->all();

        $total = 0;
        $rows = [];

        foreach ($normalizedItems as $item) {
            $product = $products[$item['product_id']] ?? null;
            $variant = $variants[$item['variant_id']] ?? null;

            if ($product === null || $variant === null || (int) $variant->product_id !== (int) $product->id) {
                $form->addError('item_product_id', 'Invalid product or variant.');
                return false;
            }

            if ((int) $variant->stock < (int) $item['quantity']) {
                $form->addError('item_product_id', 'Insufficient stock for selected variant.');
                return false;
            }

            $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

            $updated = ProductVariants::updateAllCounters(
                ['stock' => -(int) $item['quantity']],
                [
                    'and',
                    ['id' => $variant->id],
                    ['>=', 'stock', (int) $item['quantity']],
                ]
            );

            if ($updated < 1) {
                $form->addError('item_product_id', 'Insufficient stock for selected variant.');
                return false;
            }

            $rows[] = [
                $order->id,
                $product->id,
                $variant->id,
                $product->name,
                $variant->name,
                null,
                $variant->sku,
                (int) $item['quantity'],
                $price,
            ];

            $total += $price * (int) $item['quantity'];
        }

        Yii::$app->db->createCommand()->batchInsert(
            OrderItems::tableName(),
            [
                'order_id',
                'product_id',
                'variant_id',
                'product_name',
                'variant_name',
                'image_url',
                'sku',
                'quantity',
                'price',
            ],
            $rows
        )->execute();

        return $total;
    }

    private function buildItemsFromFields(CreateOrderForm $form)
    {
        if (!empty($form->order_items) && is_array($form->order_items)) {
            $items = [];
            foreach ($form->order_items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $pid = $item['product_id'] ?? null;
                $vid = $item['variant_id'] ?? null;
                $qty = $item['quantity'] ?? null;

                if ($pid && $vid && $qty) {
                    $items[] = [
                        'product_id' => (int) $pid,
                        'variant_id' => (int) $vid,
                        'quantity' => (int) $qty,
                    ];
                }
            }

            return $items;
        }

        $items = [];
        $count = max(
            count($form->item_product_id ?? []),
            count($form->item_variant_id ?? []),
            count($form->item_quantity ?? [])
        );
        for ($i = 0; $i < $count; $i++) {
            $pid = $form->item_product_id[$i] ?? null;
            $vid = $form->item_variant_id[$i] ?? null;
            $qty = $form->item_quantity[$i] ?? null;
            if ($pid && $vid && $qty) {
                $items[] = [
                    'product_id' => (int) $pid,
                    'variant_id' => (int) $vid,
                    'quantity' => (int) $qty,
                ];
            }
        }
        return $items;
    }

    private function createOrUpdatePayment(Orders $order, CreateOrderForm $form)
    {
        $payment = Payments::findOne(['order_id' => $order->id]);
        if ($payment === null) {
            $payment = new Payments();
            $payment->order_id = $order->id;
            $payment->transaction_id = strtoupper(Yii::$app->security->generateRandomString(12));
            $payment->idempotency_key = strtoupper(Yii::$app->security->generateRandomString(16));
            $payment->created_at = time();
        }
        $payment->updated_at = time();

        $payment->setAttributes([
            'amount' => $order->total,
            'payment_method' => $form->payment_method,
            'status' => $form->payment_status,
            'payment_status' => $form->payment_status,
        ], false);
        if (!$payment->save()) {
            throw new \RuntimeException('Failed to save payment: ' . json_encode($payment->errors));
        }
    }

    private function buildOrderFromForm(CreateOrderForm $form)
    {
        $order = new Orders();

        return $this->applyFormToOrder($order, $form);
    }

    private function applyFormToOrder(Orders $order, CreateOrderForm $form)
    {
        $order->setAttributes(
            [
                'user_id' => $form->user_id,
                'email' => $form->email,
                'receiver_name' => $form->receiver_name,
                'receiver_phone' => $form->receiver_phone,
                'receiver_address' => $form->receiver_address,
                'note' => $form->note,
                'status' => $form->status,
                'payment_method' => $form->payment_method,
                'payment_status' => $form->payment_status,
                'shipping_fee' => $form->shipping_fee ?? 0,
            ]
            ,
            false
        );
        return $order;
    }

    private function resolveCouponDiscount(CreateOrderForm $form, float $subTotal)
    {
        $couponCode = trim((string) $form->coupon_code);
        if ($couponCode === '') {
            return [
                'coupon' => null,
                'discount' => 0.0,
            ];
        }

        $coupon = $this->findValidCoupon($couponCode, $subTotal, $form);
        if ($coupon === null) {
            return false;
        }

        $discount = $this->calculateCouponDiscount($coupon, $subTotal);

        return [
            'coupon' => $coupon,
            'discount' => $discount,
        ];
    }

    private function findValidCoupon(string $couponCode, float $subTotal, CreateOrderForm $form)
    {
        $coupon = Coupons::find()
            ->where(['code' => $couponCode, 'is_active' => 1])
            ->one();

        if ($coupon === null) {
            $form->addError('coupon_code', 'Coupon not found or inactive.');
            return null;
        }

        $now = time();
        $startsAt = $this->parseCouponTimestamp($coupon->starts_at);
        $expiresAt = $this->parseCouponTimestamp($coupon->expires_at);

        if ($startsAt !== null && $now < $startsAt) {
            $form->addError('coupon_code', 'Coupon is not active yet.');
            return null;
        }

        if ($expiresAt !== null && $now > $expiresAt) {
            $form->addError('coupon_code', 'Coupon has expired.');
            return null;
        }

        if ($coupon->used_count >= $coupon->max_usage) {
            $form->addError('coupon_code', 'Coupon usage limit reached.');
            return null;
        }

        if ((float) $coupon->min_order_value > 0 && $subTotal < (float) $coupon->min_order_value) {
            $form->addError('coupon_code', 'Order total does not meet coupon minimum.');
            return null;
        }

        return $coupon;
    }

    private function parseCouponTimestamp($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        $timestamp = strtotime((string) $value);
        return $timestamp === false ? null : $timestamp;
    }

    private function calculateCouponDiscount(Coupons $coupon, float $subTotal): float
    {
        $type = strtolower((string) $coupon->type);
        if (in_array($type, ['percent', 'percentage', '%'], true)) {
            $discount = $subTotal * ((float) $coupon->value / 100);
        } else {
            $discount = (float) $coupon->value;
        }

        if ((float) $coupon->max_discount > 0) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return max(0, min($discount, $subTotal));
    }

    private function clearCartUser(int $userId)
    {
        $cart = Carts::findOne(['user_id' => $userId]);
        if ($cart === null) {
            return;
        }
        $cartId = $cart->id;
        CartItems::deleteAll([
            'cart_id' => $cartId,
        ]);
        // Carts::find()
        //     ->where(['user_id' => $userId])
        //     ->updateAll(['total' => 0]);
        Carts::updateAll(['total' => 0], ['user_id' => $userId]);
    }

    private function recordCouponUsage(Coupons $coupon, Orders $order, float $discountAmount, CreateOrderForm $form): bool
    {
        $usage = new CouponUsages();
        $now = time();
        $usage->setAttributes([
            'coupon_id' => $coupon->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'used_at' => $now,
            'discount_applied' => $discountAmount,
            'created_at' => $now,
            'updated_at' => $now,
        ], false);

        if (!$usage->save()) {
            $form->addError('coupon_code', 'Failed to save coupon usage.');
            return false;
        }

        $condition = [
            'and',
            ['id' => $coupon->id],
            ['<', 'used_count', (int) $coupon->max_usage],
        ];

        $updated = Coupons::updateAllCounters(['used_count' => 1], $condition);
        if ($updated < 1) {
            $form->addError('coupon_code', 'Coupon usage limit reached.');
            return false;
        }

        return true;
    }

    private function addOrderErrorsToForm(Orders $order, CreateOrderForm $form)
    {
        foreach ($order->getErrors() as $attribute => $messages) {
            foreach ($messages as $message) {
                $form->addError($attribute, $message);
            }
        }
    }
}
