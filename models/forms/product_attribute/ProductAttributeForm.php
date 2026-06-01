<?php

namespace app\models\forms\product_attribute;

use app\models\ProductAttribute;

class ProductAttributeForm extends ProductAttribute
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    public $attribute_value = [];

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['product_id', 'type', 'name', 'is_variant', 'sort_order', 'status', 'attribute_value'];
        $scenarios[self::SCENARIO_UPDATE] = ['product_id', 'type', 'name', 'is_variant', 'sort_order', 'status', 'attribute_value'];
        return $scenarios;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['attribute_value'], 'safe'],
        ]);
    }
}
