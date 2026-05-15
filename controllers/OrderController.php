<?php

namespace app\controllers;

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
use Yii;

class OrderController extends BaseController
{
    public $modelClass = 'app\models\Orders';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex()
    {
        $query = OrderResponse::find();
        $data = $this->paginate($query);
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
                $order->discount_amount = $discountAmount;
                $order->is_discounted = $coupon !== null ? 1 : 0;

                $order->total = $total + (float) $order->shipping_fee - (float) $order->discount_amount;
                $order->save(false, ['total']);

                if ($coupon !== null) {
                    if (!$this->recordCouponUsage($coupon, $order, $discountAmount, $form)) {
                        $transaction->rollBack();
                        return $this->json(false, $form->errors, 'Validation failed', 422);
                    }
                }

                $this->createOrUpdatePayment($order, $form);

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
            return $this->json(false, null, 'Internal server error', 500);
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
        $model->status = $form->status;
        $model->payment_status = $form->payment_status;
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

        return $this->json(false, null, 'Order not found', 404);
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

        return $this->json(false, null, 'Order not found', 404);
    }

    private function createOrderItems(Orders $order, CreateOrderForm $form, array $items)
    {
        $total = 0;

        foreach ($items as $item) {
            if ($item['quantity'] < 1) {
                continue;
            }

            $product = Products::findOne(['id' => $item['product_id']]);
            $variant = ProductVariants::findOne(['id' => $item['variant_id']]);

            if ($product === null || $variant === null || (int) $variant->product_id !== (int) $product->id) {
                $form->addError('item_product_id', 'Invalid product or variant.');
                return false;
            }

            if ($variant->stock === null || (int) $variant->stock < (int) $item['quantity']) {
                $form->addError('item_product_id', 'Insufficient stock for selected variant.');
                return false;
            }

            $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

            $orderItem = new OrderItems();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product->id;
            $orderItem->variant_id = $variant->id;
            $orderItem->product_name = $product->name;
            $orderItem->variant_name = $variant->name;
            $orderItem->sku = $variant->sku;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $price;
            if (!$orderItem->save()) {
                $form->addError('item_product_id', 'Failed to save order item.');
                return false;
            }

            $variant->stock = (int) $variant->stock - (int) $item['quantity'];
            if (!$variant->save(false, ['stock'])) {
                $form->addError('item_product_id', 'Failed to update variant stock.');
                return false;
            }

            $total += $price * $item['quantity'];
        }

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

        $payment->amount = $order->total;
        $payment->payment_method = $form->payment_method;
        $payment->status = $form->payment_status;
        $payment->payment_status = $form->payment_status;
        $payment->save();
    }

    private function buildOrderFromForm(CreateOrderForm $form)
    {
        $order = new Orders();

        return $this->applyFormToOrder($order, $form);
    }

    private function applyFormToOrder(Orders $order, CreateOrderForm $form)
    {
        $order->user_id = $form->user_id;
        $order->email = $form->email;
        $order->receiver_name = $form->receiver_name;
        $order->receiver_phone = $form->receiver_phone;
        $order->receiver_address = $form->receiver_address;
        $order->note = $form->note;
        $order->is_discounted = $form->is_discounted;
        $order->shipping_fee = $form->shipping_fee;
        $order->discount_amount = $form->discount_amount;
        $order->payment_method = $form->payment_method;
        $order->payment_status = $form->payment_status;
        $order->status = $form->status;

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
        $startsAt = is_numeric($coupon->starts_at) ? (int) $coupon->starts_at : strtotime($coupon->starts_at);
        $expiresAt = is_numeric($coupon->expires_at) ? (int) $coupon->expires_at : strtotime($coupon->expires_at);

        if ($startsAt !== false && $now < $startsAt) {
            $form->addError('coupon_code', 'Coupon is not active yet.');
            return null;
        }

        if ($expiresAt !== false && $now > $expiresAt) {
            $form->addError('coupon_code', 'Coupon has expired.');
            return null;
        }

        if ($coupon->max_usage !== null && $coupon->used_count >= $coupon->max_usage) {
            $form->addError('coupon_code', 'Coupon usage limit reached.');
            return null;
        }

        if ((float) $coupon->min_order_value > 0 && $subTotal < (float) $coupon->min_order_value) {
            $form->addError('coupon_code', 'Order total does not meet coupon minimum.');
            return null;
        }

        return $coupon;
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

    private function recordCouponUsage(Coupons $coupon, Orders $order, float $discountAmount, CreateOrderForm $form): bool
    {
        $usage = new CouponUsages();
        $usage->coupon_id = $coupon->id;
        $usage->user_id = $order->user_id;
        $usage->order_id = $order->id;
        $usage->used_at = time();
        $usage->discount_applied = $discountAmount;
        $usage->created_at = time();
        $usage->updated_at = time();

        if (!$usage->save()) {
            $form->addError('coupon_code', 'Failed to save coupon usage.');
            return false;
        }

        $coupon->used_count = (int) $coupon->used_count + 1;
        if (!$coupon->save(false, ['used_count'])) {
            $form->addError('coupon_code', 'Failed to update coupon usage count.');
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
