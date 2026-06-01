<?php

namespace app\controllers;

use app\models\User;
use app\models\forms\auth\RegisterForm;
use Yii;

class AuthController extends BaseController
{
    public function actionRegister()
    {
        $form = new RegisterForm([
            'scenario' => RegisterForm::SCENARIO_REGISTER,
        ]);

        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $user = new User();

        $user->username = $form->username;
        $user->email = $form->email;
        $user->phone_number = $form->phone_number;
        $user->setPassword($form->password);

        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->access_token = Yii::$app->security->generateRandomString(64);
        try {
            if ($user->save(false)) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('user');

                if ($role) {
                    $auth->assign($role, $user->id);
                }
                return $this->formatJson(true, [
                    'id' => $user->id,
                    'access_token' => $user->access_token,
                ], 'Register success', 201);
            }
        } catch (\Exception $e) {
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }


        $err = $user->getErrors();

        return $this->formatJson(false, $err, 'Register failed', 500);


    }

    public function actionLogin()
    {

    }

    public function actionLogout()
    {

    }
}
