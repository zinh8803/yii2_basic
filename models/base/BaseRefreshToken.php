<?php

namespace app\models\base;

use app\models\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "refresh_tokens".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string|null $device_name
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $expires_at
 * @property int|null $revoked_at
 * @property int|null $last_used_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class BaseRefreshToken extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'refresh_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_name', 'ip_address', 'user_agent', 'revoked_at', 'last_used_at'], 'default', 'value' => null],
            [['user_id', 'token', 'expires_at', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'expires_at', 'revoked_at', 'last_used_at', 'created_at', 'updated_at'], 'integer'],
            [['token', 'device_name', 'user_agent'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 45],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'token' => 'Token',
            'device_name' => 'Device Name',
            'ip_address' => 'Ip Address',
            'user_agent' => 'User Agent',
            'expires_at' => 'Expires At',
            'revoked_at' => 'Revoked At',
            'last_used_at' => 'Last Used At',
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
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
