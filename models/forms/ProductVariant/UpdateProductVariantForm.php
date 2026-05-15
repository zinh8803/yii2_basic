<?php
namespace app\models\forms\ProductVariant;

use yii\base\Model;

class UpdateProductVariantForm extends Model
{
    public $id;
    public $product_id;
    public $name;
    public $sku;
    public $price;
    public $sale_price;
    public $cost_price;
    public $stock;
    public $weight;
    public $is_active;
    public function rules()
    {
        return [
            [['id', 'product_id', 'name', 'price', 'sale_price', 'cost_price', 'stock'], 'required'],
            [['id', 'product_id', 'stock'], 'integer'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
            [['is_active'], 'boolean'],
            [['name', 'sku'], 'string', 'max' => 255],
        ];
    }

}
