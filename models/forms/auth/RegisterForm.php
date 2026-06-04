<?php
namespace app\models\forms\auth;
use app\models\User;
use Yii;

class RegisterForm extends User
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['username', 'email', 'password', 'phone_number'], 'required'],
            [['email'], 'email'],
            [['username'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
            [['auth_key'], 'unique'],
            [['access_token'], 'unique'],
        ]);
    }
}
