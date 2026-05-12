<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "resources".
 *
 * @property int $id
 * @property int $file_id
 * @property string $resource_type
 * @property int $resource_id
 * @property string $type
 * @property string $title
 * @property string|null $alt_text
 * @property int $sort_order
 * @property int $is_primary
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Files $file
 */
class Resources extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alt_text'], 'default', 'value' => null],
            [['is_primary'], 'default', 'value' => 0],
            [['file_id', 'resource_type', 'resource_id', 'type', 'title'], 'required'],
            [['file_id', 'resource_id', 'sort_order', 'is_primary'], 'integer'],
            [['resource_type', 'title', 'alt_text'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::class, 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_id' => 'File ID',
            'resource_type' => 'Resource Type',
            'resource_id' => 'Resource ID',
            'type' => 'Type',
            'title' => 'Title',
            'alt_text' => 'Alt Text',
            'sort_order' => 'Sort Order',
            'is_primary' => 'Is Primary',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'resource_type',
            'resource_id',
            'type',
            'title',
            'alt_text',
            'sort_order',
            'is_primary',
            'file',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(Files::class, ['id' => 'file_id']);
    }

}
