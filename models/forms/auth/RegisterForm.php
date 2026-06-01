<?php
namespace app\models\forms\auth;
use app\models\User;

class RegisterForm extends User
{
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        return [
            self::SCENARIO_REGISTER => ['username', 'email', 'password', 'phone_number'],
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['username', 'email', 'password', 'phone_number'], 'required', 'on' => self::SCENARIO_REGISTER],
            [['email'], 'email'],
            [['username'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
        ]);
    }
}
