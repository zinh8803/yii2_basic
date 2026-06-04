<?php
namespace app\components;

use Yii;
use yii\web\ErrorHandler;
use yii\web\HttpException;
use yii\web\Response;

class ApiErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        Yii::$app->response->statusCode = $exception instanceof HttpException
            ? $exception->statusCode
            : 500;

        Yii::$app->response->data = [
            'code' => Yii::$app->response->statusCode,
            'status' => false,
            'data' => null,
            'message' => $exception->getMessage(),
            'error' => YII_DEBUG ? $exception->getTraceAsString() : null,
        ];

        Yii::$app->response->send();
    }
}
