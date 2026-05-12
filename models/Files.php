<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property int $user_id
 * @property string $disk
 * @property string $path
 * @property string $url
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property int|null $width
 * @property int|null $height
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Resources[] $resources
 * @property Users $user
 */
class Files extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['width', 'height'], 'default', 'value' => null],
            [['user_id', 'disk', 'path', 'url', 'original_name', 'mime_type', 'size_bytes'], 'required'],
            [['user_id', 'size_bytes', 'width', 'height'], 'integer'],
            [['disk'], 'string', 'max' => 50],
            [['path', 'url', 'original_name', 'mime_type'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'disk' => 'Disk',
            'path' => 'Path',
            'url' => 'Url',
            'original_name' => 'Original Name',
            'mime_type' => 'Mime Type',
            'size_bytes' => 'Size Bytes',
            'width' => 'Width',
            'height' => 'Height',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Resources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resources::class, ['file_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

}
