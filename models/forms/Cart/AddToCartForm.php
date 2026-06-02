<?php

namespace app\models\forms\Cart;

use yii\base\Model;

class AddToCartForm extends Model
{
    public $user_id;
    public $items;

    public function rules()
    {
        return [
            [['user_id', 'items'], 'required'],
            [['user_id'], 'integer'],
            [['items'], 'validateItems'],
        ];
    }

    public function validateItems($attribute): void
    {
        if (!is_array($this->$attribute) || empty($this->$attribute)) {
            $this->addError($attribute, 'Items must be a non-empty array.');
            return;
        }

        foreach ($this->$attribute as $index => $item) {
            if (!is_array($item)) {
                $this->addError($attribute, "Items[$index] must be an object.");
                continue;
            }

            foreach (['product_id', 'product_variant_id', 'quantity'] as $field) {
                if (!array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                    $this->addError($attribute, "Items[$index].$field cannot be blank.");
                    continue;
                }

                if (filter_var($item[$field], FILTER_VALIDATE_INT) === false) {
                    $this->addError($attribute, "Items[$index].$field must be an integer.");
                }
            }

            if (isset($item['quantity']) && filter_var($item['quantity'], FILTER_VALIDATE_INT) !== false && (int) $item['quantity'] < 1) {
                $this->addError($attribute, "Items[$index].quantity must be no less than 1.");
            }
        }
    }

    public function getItems(): array
    {
        return array_map(static function (array $item): array {
            return [
                'product_id' => (int) $item['product_id'],
                'product_variant_id' => (int) $item['product_variant_id'],
                'quantity' => (int) $item['quantity'],
            ];
        }, $this->items);
    }
}
