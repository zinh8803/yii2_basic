<?php

namespace app\controllers;

use Yii;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends BaseController
{
    public $modelClass = 'app\\models\\User';


    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;

        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        // hash md5
        $passwordMd5 = md5($password);

        if ($username === 'admin' && $passwordMd5 === md5('123456')) {
            return $this->json(true, [
                'id' => 1,
                'username' => 'admin',
                'name' => 'Ngô Quốc Vinh',
                'nows' => '' . date('Y-m-d'),
            ], 'Success');
        }

        return $this->json(false, null, 'Sai tài khoản hoặc mật khẩu', 401);
    }


}
