<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\ContactForm;
use app\models\LoginForm;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Security;
use yii\mail\MailerInterface;
use yii\web\ErrorAction;

class SiteController extends BaseController
{
    public $modelClass = 'yii\\base\\Model';

    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    public function actionIndex(): array
    {
        return $this->json(true, null, 'Welcome');
    }


    public function actionLogin(): array
    {
        if (!Yii::$app->user->isGuest) {
            return $this->json(true, null, 'Already logged in');
        }

        $model = new LoginForm($this->security);
        $model->load($this->request->bodyParams, '');

        if ($model->login()) {
            return $this->json(true, null, 'Login success');
        }

        return $this->json(false, $model->errors, 'Login failed', 422);
    }

    public function actionLogout(): array
    {
        Yii::$app->user->logout();
        return $this->json(true, null, 'Logout success');
    }

    public function actionContact(): array
    {
        $model = new ContactForm();
        $contact = $model->load($this->request->bodyParams, '') && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            return $this->json(true, null, 'Contact sent successfully');
        }

        return $this->json(false, $model->errors, 'Contact failed', 422);
    }

    public function actionAbout(): array
    {
        return $this->json(true, null, 'About');
    }
}
