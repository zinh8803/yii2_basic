<?php
namespace app\models\forms\ProductVariant;

use yii\base\Model;

class CreateProductVariantForm extends Model
{
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
            [['product_id', 'name', 'sku', 'price', 'sale_price', 'cost_price', 'stock'], 'required'],
            [['product_id', 'stock'], 'integer'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
            [['name', 'sku'], 'string', 'max' => 255],
        ];
    }

}
