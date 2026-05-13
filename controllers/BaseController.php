<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;

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

    public function paginate($query, int $defaultLimit = 10): array
    {
        $page = (int) Yii::$app->request->get('page', 1);
        $limit = (int) Yii::$app->request->get('limit', $defaultLimit);

        if ($page < 1) {
            $page = 1;
        }

        if ($limit < 1) {
            $limit = $defaultLimit;
        }

        $total = (clone $query)->count();

        $data = $query
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->all();

        return [
            'items' => $data,
            'pagination' => [
                'total' => (int) $total,
                'page' => $page,
                'limit' => $limit,
                'total_page' => (int) ceil($total / $limit),
            ],
        ];
    }
}
