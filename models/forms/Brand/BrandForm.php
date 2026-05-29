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
            self::SCENARIO_CREATE => ['name', 'status'],
            self::SCENARIO_UPDATE => ['name', 'status'],
        ];
    }
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1, 'on' => self::SCENARIO_CREATE],
            [['name'], 'required', 'on' => self::SCENARIO_CREATE],

            [['status'], 'integer'],
            [['status'], 'in', 'range' => [0, 1]],

            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }
}
