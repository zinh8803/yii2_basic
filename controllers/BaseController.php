<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;

class BaseController extends ActiveController
{
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }
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
        $maxLimit = 100;

        if ($page < 1) {
            $page = 1;
        }

        if ($limit < 1) {
            $limit = $defaultLimit;
        }
        if ($limit > $maxLimit) {
            $limit = $maxLimit;
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
