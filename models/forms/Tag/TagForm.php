<?php

namespace app\models\forms\Tag;

use app\models\Tag;

class TagForm extends Tag
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name', 'type', 'description'],
            self::SCENARIO_UPDATE => ['name', 'type', 'description']
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => Tag::class, 'targetAttribute' => 'name', 'on' => self::SCENARIO_CREATE],
            [['slug'], 'unique', 'targetClass' => Tag::class, 'targetAttribute' => 'slug', 'filter' => ['!=', 'id', $this->id], 'on' => self::SCENARIO_UPDATE],
        ]);
    }
}
