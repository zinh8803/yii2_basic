<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Taggables[] $taggables
 */
class Tags extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'slug', 'type', 'description', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'slug', 'type', 'description'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique'],
            [['description'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'type' => 'Type',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Taggables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaggables()
    {
        return $this->hasMany(Taggables::class, ['tag_id' => 'id']);
    }

}
