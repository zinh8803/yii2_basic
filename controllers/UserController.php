<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{

    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;

        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        // hash md5
        $passwordMd5 = md5($password);

        if ($username === 'admin' && $passwordMd5 === md5('123456')) {

            return [
                'status' => true,
                'data' => [
                    'id' => 1,
                    'username' => 'admin',
                    'name' => 'Ngô Quốc Vinh',
                    "nows" => '' . date('Y-m-d'),
                ],
                "message" => "Success",
            ];
        }

        Yii::$app->response->statusCode = 401;

        return [
            'status' => false,
            'message' => 'Sai tài khoản hoặc mật khẩu'
        ];
    }


}
