<?php

namespace app\models\forms\ProductAttribute;

use app\models\Products;
use yii\base\Model;

class CreateProductAttribute extends Model
{
    public $product_id;
    public $name;
    public $type;
    public $slug;
    public $is_variant;
    public $sort_order;
    public $attribute_value = [];
    public $created_at;
    public $updated_at;

    public function rules()
    {
        return [
            [['slug'], 'default', 'value' => null],
            [['type'], 'default', 'value' => 'text'],
            [['sort_order'], 'default', 'value' => 0],
            [['product_id', 'name'], 'required'],
            [['product_id', 'is_variant', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],

            [['attribute_value'], 'safe'],
        ];
    }
}
