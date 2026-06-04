<?php

namespace app\models\forms\category;

use app\models\Category;

class CategoryForm extends Category
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name', 'parent_id', 'status', 'is_deleted'],
            self::SCENARIO_UPDATE => ['id', 'name', 'parent_id', 'status'],
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'required', 'on' => self::SCENARIO_CREATE],
            [['status', 'is_deleted'], 'default', 'value' => 0, 'on' => self::SCENARIO_CREATE],
            [['status'], 'in', 'range' => [0, 1]],
            [['name'], 'unique', 'targetClass' => Category::class, 'targetAttribute' => 'name', 'on' => self::SCENARIO_CREATE,],
            [['name'], 'unique', 'targetClass' => Category::class, 'targetAttribute' => 'name', 'filter' => ['!=', 'id', $this->id], 'on' => self::SCENARIO_UPDATE],

        ]);
    }
}
