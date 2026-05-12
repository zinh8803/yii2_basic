<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_addresses".
 *
 * @property int $id
 * @property int $user_id
 * @property string $city
 * @property string $ward
 * @property string $detail_address
 * @property string $phone_number
 * @property string $name_address
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 */
class UserAddresses extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_addresses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'city', 'ward', 'detail_address', 'phone_number', 'name_address'], 'required'],
            [['user_id'], 'integer'],
            [['city', 'ward'], 'string', 'max' => 100],
            [['detail_address', 'name_address'], 'string', 'max' => 255],
            [['phone_number'], 'string', 'max' => 20],
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
            'city' => 'City',
            'ward' => 'Ward',
            'detail_address' => 'Detail Address',
            'phone_number' => 'Phone Number',
            'name_address' => 'Name Address',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
