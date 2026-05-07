<?php

namespace app\controllers;

use Exception;
use Yii;
use yii\rest\Controller as BaseController;
class CalculateController extends BaseController
{
    public function actionTotal()
    {
        $body = Yii::$app->request->bodyParams;
        $a = $body['a'];
        $b = $body['b'];
        if (!is_numeric($a) || !is_numeric($b)) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => false,
                'data' => null,
                'message' => 'a and b must be numbers',
            ];
        }
        $result = (float) $a + (float) $b;

        return [
            'status' => true,
            'data' => [
                'result' => $result,
            ],
            "message" => "Success",
        ];

    }
    public function actionDivide()
    {
        try {
            $body = Yii::$app->request->bodyParams;
            $a = $body['a'];
            $b = $body['b'];

            if (!is_numeric($a) || !is_numeric($b)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'a and b must be numbers',
                ];
            }

            if ($b === 0) {
                Yii::$app->response->statusCode = 400;
                return [
                    'status' => false,
                    'data' => null,
                    'message' => 'b cannot be zero',
                ];
            }

            $result = (float) $a / (float) $b;

            return [
                'status' => true,
                'data' => [
                    'result' => $result,
                ],
                "message" => "Success",
            ];
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 500;
            return [
                'status' => false,
                'data' => null,
                'message' => 'Internal Server Error',
            ];
        }
    }
    public function actionAverage()
    {
        $body = Yii::$app->request->bodyParams;
        $numbers = $body['numbers'] ?? [];

        if (!is_array($numbers)) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => false,
                'data' => null,
                'message' => 'số liệu phải là một mảng',
            ];
        }

        $flatNumbers = [];
        $error = null;
        $this->collectNumbers($numbers, $flatNumbers, $error);

        if ($error !== null) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => false,
                'data' => null,
                'message' => $error,
            ];
        }

        $sum = array_sum($flatNumbers);
        $count = count($flatNumbers);

        if ($count === 0) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => false,
                'data' => null,
                'message' => 'Mảng số liệu trống.',
            ];
        }

        $average = $sum / $count;

        return [
            'status' => true,
            'data' => [
                'average' => $average,
            ],
            "message" => "Success",
        ];
    }

    private function collectNumbers($value, array &$results, ?string &$error): void
    {
        if ($error !== null) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->collectNumbers($item, $results, $error);
                if ($error !== null) {
                    return;
                }
            }
            return;
        }

        if (!is_numeric($value)) {
            $error = 'Mảng chỉ được chứa số, chuỗi số hoặc mảng con chứa số.';
            return;
        }

        $results[] = (float) $value;
    }
}