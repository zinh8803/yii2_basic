<?php

namespace app\models\forms\brand;

use app\models\Brand;

class BrandForm extends Brand
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name', 'status', 'is_deleted'],
            self::SCENARIO_UPDATE => ['name', 'status'],
        ];
    }

    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1, 'on' => self::SCENARIO_CREATE],
            [['name'], 'required', 'on' => self::SCENARIO_CREATE],
            [['status', 'is_deleted'], 'default', 'value' => 0, 'on' => self::SCENARIO_CREATE],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => [0, 1]],
            [['name'], 'unique', 'targetClass' => Brand::class, 'targetAttribute' => 'name', 'on' => self::SCENARIO_CREATE,],
            [['name'], 'unique', 'targetClass' => Brand::class, 'targetAttribute' => 'name', 'filter' => (['!=', 'id', $this->id]), 'on' => self::SCENARIO_UPDATE,],
        ];
    }
}
