<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

class BaseController extends Controller
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    public function formatJson($status = true, $data = [], $message = "", $code = 200): array
    {
        Yii::$app->response->statusCode = $code;

        return [
            "code" => $code,
            "status" => $status,
            "data" => $data,
            "message" => $message,
            "error" => null,
        ];
    }
    protected function successPaginate(ActiveDataProvider $dataProvider, string $message = 'Data retrieved successfully', $statusCode = 200): array
    {
        return [
            'code' => $statusCode,
            'status' => true,
            'data' => $dataProvider->getModels(),
            '_meta' => [
                'totalCount' => $dataProvider->getTotalCount(),
                'pageCount' => $dataProvider->pagination->getPageCount(),
                'currentPage' => $dataProvider->pagination->getPage() + 1,
                'perPage' => $dataProvider->pagination->getPageSize(),
            ],
            'message' => $message,
            'error' => null,
        ];
    }
    protected function checkPermission(string $permission): void
    {
        if (!Yii::$app->user->can($permission)) {
            throw new ForbiddenHttpException('Permission denied');
        }
    }
}
