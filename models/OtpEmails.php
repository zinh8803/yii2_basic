<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "otp_emails".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string $otp_code
 * @property string $type
 * @property int $expired_at
 * @property int $verified_at
 * @property int $attempts
 * @property int $max_attempts
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 */
class OtpEmails extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'otp_emails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'ip_address', 'user_agent'], 'default', 'value' => null],
            [['attempts'], 'default', 'value' => 0],
            [['max_attempts'], 'default', 'value' => 5],
            [['user_id', 'expired_at', 'verified_at', 'attempts', 'max_attempts', 'created_at', 'updated_at'], 'integer'],
            [['email', 'otp_code', 'type', 'expired_at', 'verified_at', 'created_at', 'updated_at'], 'required'],
            [['email', 'user_agent'], 'string', 'max' => 255],
            [['otp_code'], 'string', 'max' => 10],
            [['type'], 'string', 'max' => 50],
            [['ip_address'], 'string', 'max' => 45],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'email' => 'Email',
            'otp_code' => 'Otp Code',
            'type' => 'Type',
            'expired_at' => 'Expired At',
            'verified_at' => 'Verified At',
            'attempts' => 'Attempts',
            'max_attempts' => 'Max Attempts',
            'ip_address' => 'Ip Address',
            'user_agent' => 'User Agent',
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
