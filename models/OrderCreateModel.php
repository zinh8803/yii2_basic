<?php

namespace app\models;

use app\models\forms\Order\CreateOrderForm;
use Yii;

class OrderCreateModel extends Order
{
    public function create(CreateOrderForm $form): ?Order
    {
        $userId = Yii::$app->user->id;
        $items = $form->getOrderItems();
        if (empty($items)) {
            $form->addError('order_items', 'Order item is required.');
            return null;
        }

        $preparedItems = $this->prepareOrderItems($form, $items);
        if ($preparedItems === null) {
            return null;
        }

        $subTotal = $preparedItems['sub_total'];
        $couponResult = $this->resolveCouponDiscount($form, $subTotal);
        if ($couponResult === null) {
            return null;
        }

        $coupon = $couponResult['coupon'];
        $discountAmount = $couponResult['discount'];

        $order = $this->buildOrderFromForm($form);
        $order->setAttributes([
            'discount_amount' => $discountAmount,
            'is_discounted' => $coupon !== null ? 1 : 0,
            'total' => max(0, $subTotal + (float) $order->shipping_fee - $discountAmount),
        ], false);

        if (!$order->save()) {
            $this->addOrderErrorsToForm($order, $form);
            return null;
        }

        if (!$this->createOrderItems($order, $form, $preparedItems['items'])) {
            return null;
        }

        if ($coupon !== null && !$this->recordCouponUsage($coupon, $order, $discountAmount, $form)) {
            return null;
        }

        $this->createOrUpdatePayment($order, $form);
        $this->clearCartUser($userId);

        $order->populateRelation('orderItems', $this->findOrderItems($order->id));

        return $order;
    }

    private function buildOrderFromForm(CreateOrderForm $form): Order
    {
        $userId = Yii::$app->user->id;
        $order = new Order();
        $order->setAttributes([
            'user_id' => $userId,
            'email' => $form->email,
            'receiver_name' => $form->receiver_name,
            'receiver_phone' => $form->receiver_phone,
            'receiver_address' => $form->receiver_address,
            'note' => $form->note,
            'status' => $form->status,
            'payment_method' => $form->payment_method,
            'payment_status' => 'pending',
            'shipping_fee' => $form->shipping_fee ?? 0,
        ], false);

        return $order;
    }

    private function prepareOrderItems(CreateOrderForm $form, array $items): ?array
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            $key = (int) $item['product_id'] . ':' . (int) $item['variant_id'];
            if (!isset($normalizedItems[$key])) {
                $normalizedItems[$key] = [
                    'product_id' => (int) $item['product_id'],
                    'variant_id' => (int) $item['variant_id'],
                    'quantity' => 0,
                ];
            }
            $normalizedItems[$key]['quantity'] += (int) $item['quantity'];
        }

        if (empty($normalizedItems)) {
            $form->addError('order_items', 'Order item is required.');
            return null;
        }

        $variantIds = array_values(array_unique(array_column($normalizedItems, 'variant_id')));

        $variants = ProductVariant::find()
            ->with(['product'])
            ->where(['id' => $variantIds])
            ->indexBy('id')
            ->all();

        $total = 0.0;
        $preparedItems = [];

        foreach ($normalizedItems as $item) {
            $variant = $variants[$item['variant_id']] ?? null;
            $product = $variant?->product;

            if ($variant === null || $product === null || (int) $variant->product_id !== (int) $item['product_id']) {
                $form->addError('order_items', 'Invalid product or variant.');
                return null;
            }

            if ((int) $variant->stock < (int) $item['quantity']) {
                $form->addError('order_items', 'Insufficient stock for selected variant.');
                return null;
            }

            $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
            $preparedItems[] = [
                'variant' => $variant,
                'product' => $product,
                'quantity' => (int) $item['quantity'],
                'price' => $price,
            ];

            $total += (float) $price * (int) $item['quantity'];
        }

        return [
            'sub_total' => $total,
            'items' => $preparedItems,
        ];
    }

    private function createOrderItems(Order $order, CreateOrderForm $form, array $items): bool
    {
        $rows = [];

        foreach ($items as $item) {
            /** @var ProductVariant $variant */
            $variant = $item['variant'];
            /** @var Product $product */
            $product = $item['product'];
            $quantity = (int) $item['quantity'];

            if ((int) $variant->stock < $quantity) {
                $form->addError('order_items', 'Insufficient stock for selected variant.');
                return false;
            }

            $variant->stock = (int) $variant->stock - $quantity;
            if (!$variant->save(true, ['stock', 'updated_at'])) {
                $form->addError('order_items', 'Failed to update variant stock.');
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
                $quantity,
                $item['price'],
            ];
        }

        Yii::$app->db->createCommand()->batchInsert(
            OrderItem::tableName(),
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

        return true;
    }

    private function resolveCouponDiscount(CreateOrderForm $form, float $subTotal): ?array
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
            return null;
        }

        return [
            'coupon' => $coupon,
            'discount' => $this->calculateCouponDiscount($coupon, $subTotal),
        ];
    }

    private function findValidCoupon(string $couponCode, float $subTotal, CreateOrderForm $form): ?Coupon
    {
        $coupon = Coupon::find()
            ->where(['code' => $couponCode, 'is_active' => 1])
            ->one();

        if ($coupon === null) {
            $form->addError('coupon_code', 'Coupon not found or inactive.');
            return null;
        }

        $now = time();
        if ($now < (int) $coupon->starts_at) {
            $form->addError('coupon_code', 'Coupon is not active yet.');
            return null;
        }

        if ($now > (int) $coupon->expires_at) {
            $form->addError('coupon_code', 'Coupon has expired.');
            return null;
        }

        if ((int) $coupon->used_count >= (int) $coupon->max_usage) {
            $form->addError('coupon_code', 'Coupon usage limit reached.');
            return null;
        }

        if ((float) $coupon->min_order_value > 0 && $subTotal < (float) $coupon->min_order_value) {
            $form->addError('coupon_code', 'Order total does not meet coupon minimum.');
            return null;
        }

        return $coupon;
    }

    private function calculateCouponDiscount(Coupon $coupon, float $subTotal): float
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

    private function recordCouponUsage(Coupon $coupon, Order $order, float $discountAmount, CreateOrderForm $form): bool
    {
        $userId = Yii::$app->user->id;
        $usage = new CouponUsage();
        $usage->setAttributes([
            'coupon_id' => $coupon->id,
            'user_id' => $userId,
            'order_id' => $order->id,
            'used_at' => time(),
            'discount_applied' => $discountAmount,
        ], false);

        if (!$usage->save()) {
            $form->addError('coupon_code', 'Failed to save coupon usage.');
            return false;
        }

        if ((int) $coupon->used_count >= (int) $coupon->max_usage) {
            $form->addError('coupon_code', 'Coupon usage limit reached.');
            return false;
        }

        $coupon->used_count = (int) $coupon->used_count + 1;
        if (!$coupon->save()) {
            $form->addError('coupon_code', 'Failed to update coupon usage count.');
            return false;
        }

        return true;
    }

    private function createOrUpdatePayment(Order $order, CreateOrderForm $form): void
    {
        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->transaction_id = strtoupper(Yii::$app->security->generateRandomString(12));
        $payment->idempotency_key = strtoupper(Yii::$app->security->generateRandomString(16));

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

    private function clearCartUser(int $userId): void
    {
        $cartIds = Cart::find()
            ->select('id')
            ->where(['user_id' => $userId]);

        CartItem::deleteAll(['in', 'cart_id', $cartIds]);
        Cart::updateAll(['total' => 0], ['user_id' => $userId]);
    }

    private function addOrderErrorsToForm(Order $order, CreateOrderForm $form): void
    {
        foreach ($order->getErrors() as $attribute => $messages) {
            foreach ($messages as $message) {
                $form->addError($attribute, $message);
            }
        }
    }

    private function findOrderItems(int $orderId): array
    {
        return OrderItem::find()
            ->where(['order_id' => $orderId])
            ->all();
    }
}
