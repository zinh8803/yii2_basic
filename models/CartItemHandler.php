<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseCart;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CartItemHandler extends BaseCart
{
    public function behaviors(): array
    {
        return [
            Timestamp::class,
        ];
    }

    public function addToCart(int $userId, int $productId, int $productVariantId, int $quantity): self
    {
        $cart = $this->getOrCreateByUserId($userId);
        $cart->addItem($productId, $productVariantId, $quantity);
        $cart->recalculateTotal();

        return $cart;
    }

    public function addItemsToCart(int $userId, array $items): self
    {
        $cart = $this->getOrCreateByUserId($userId);
        foreach ($items as $item) {
            $cart->addItem($item['product_id'], $item['product_variant_id'], $item['quantity']);
        }
        $cart->recalculateTotal();

        return $cart;
    }

    public function getOrCreateByUserId(int $userId): self
    {
        $cart = self::findOne(['user_id' => $userId]);
        if ($cart !== null) {
            return $cart;
        }

        $cart = new self();
        $cart->setAttributes([
            'user_id' => $userId,
            'total' => 0,
        ], false);

        if (!$cart->save()) {
            throw new \RuntimeException('Failed to create cart: ' . json_encode($cart->errors));
        }

        return $cart;
    }

    public function removeItemsFromCart(int $userId, array $itemIds): array
    {
        $cart = self::findOne(['user_id' => $userId]);
        if ($cart === null) {
            throw new NotFoundHttpException('Cart not found');
        }

        $itemIds = array_values(array_filter(array_map('intval', $itemIds), static fn($id) => $id > 0));
        if (empty($itemIds)) {
            throw new BadRequestHttpException('item_ids must contain positive integers');
        }

        $existingItemCount = CartItem::find()
            ->where([
                'id' => $itemIds,
                'cart_id' => $cart->id,
            ])
            ->count();
        if ((int) $existingItemCount === 0) {
            throw new NotFoundHttpException('Cart items not found for this user');
        }

        $deleted = CartItem::deleteAll([
            'id' => $itemIds,
            'cart_id' => $cart->id,
        ]);

        $cart->recalculateTotal();

        return [
            'deleted' => $deleted,
            'cart' => $cart,
        ];
    }

    public function addItem(int $productId, int $productVariantId, int $quantity): CartItem
    {
        $variant = ProductVariant::findOne(['id' => $productVariantId]);
        if ($variant === null || $variant->product_id !== $productId) {
            throw new NotFoundHttpException('Product variant not found');
        }

        $cartItem = CartItem::findOne([
            'cart_id' => $this->id,
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
        ]);

        $currentQuantity = $cartItem === null ? 0 : $cartItem->quantity;
        $newQuantity = $currentQuantity + $quantity;
        if ($newQuantity > $variant->stock) {
            throw new BadRequestHttpException('Requested quantity exceeds stock. Available stock: ' . $variant->stock);
        }

        $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

        if ($cartItem === null) {
            $cartItem = new CartItem();
            $cartItem->setAttributes([
                'cart_id' => $this->id,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'quantity' => $quantity,
                'price' => $price,
            ], false);
        } else {
            $cartItem->setAttributes([
                'quantity' => $newQuantity,
                'price' => $price,
            ], false);
        }

        if (!$cartItem->save()) {
            throw new \RuntimeException('Failed to save cart item: ' . json_encode($cartItem->errors));
        }

        return $cartItem;
    }

    public function recalculateTotal(): void
    {
        $total = (float) CartItem::find()
            ->where(['cart_id' => $this->id])
            ->sum('price * quantity');

        $this->setAttributes(['total' => $total], false);
        if (!$this->save(false, ['total', 'updated_at'])) {
            throw new \RuntimeException('Failed to update cart total.');
        }
    }
}
