<?php

namespace app\models\forms\ProductAttribute;

use app\models\ProductAttributes;
use app\models\Products;
use yii\base\Model;

class UpdateProductAttribute extends Model
{
    public $id;
    public $product_id;
    public $name;
    public $type;
    public $slug;
    public $is_variant;
    public $sort_order;
    public $attribute_value = [];

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['slug'], 'default', 'value' => null],
            [['type'], 'default', 'value' => 'text'],
            [['sort_order'], 'default', 'value' => 0],
            [['product_id', 'name'], 'required'],
            [['product_id', 'is_variant', 'sort_order'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [
                ['name'],
                'unique',
                'targetClass' => ProductAttributes::class,
                'targetAttribute' => 'name',
                'filter' => function ($query) {
                    if ($this->id !== null) {
                        $query->andWhere(['<>', 'id', $this->id]);
                    }
                },
            ],
            [
                ['slug'],
                'unique',
                'targetClass' => ProductAttributes::class,
                'targetAttribute' => 'slug',
                'filter' => function ($query) {
                    if ($this->id !== null) {
                        $query->andWhere(['<>', 'id', $this->id]);
                    }
                },
            ],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
            [['attribute_value'], 'safe'],
        ];
    }
}
