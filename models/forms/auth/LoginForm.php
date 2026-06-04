<?php

namespace app\models\forms\auth;

use app\models\User;

class LoginForm extends User
{
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
        ];
    }
}
