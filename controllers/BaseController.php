<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller as Controller;

class BaseController extends Controller
{
    public function json($status = true, $data = [], $message = "", $code = 200): array
    {
        Yii::$app->response->statusCode = $code;
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message,
            "code" => $code
        ];
    }

}
