<?php

namespace app\controllers;

use app\models\forms\auth\LoginForm;
use app\models\User;
use app\models\forms\auth\RegisterForm;
use Yii;

class AuthController extends BaseController
{
    public function actionRegister()
    {
        $form = new RegisterForm();

        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $user = new User();
        $user->username = $form->username;
        $user->email = $form->email;
        $user->phone_number = $form->phone_number;
        $user->setPassword($form->password);
        try {
            if ($user->save(false)) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('user');

                if ($role) {
                    $auth->assign($role, $user->id);
                }
                return $this->formatJson(true, [], 'Register success', 201);
            }
        } catch (\Exception $e) {
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }
        $err = $user->getErrors();
        return $this->formatJson(false, $err, 'Register failed', 500);
    }

    public function actionLogin()
    {
        $form = new LoginForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $user = User::findByEmail($form->email);

        if (!$user || !$user->validatePassword($form->password)) {
            return $this->formatJson(false, null, 'Invalid credentials', 401);
        }

        return $this->formatJson(true, [
            'user_id' => $user->id,
            'access_token' => $user->access_token,
        ], 'Login success', 200);
    }

    public function actionLogout()
    {

    }
}
