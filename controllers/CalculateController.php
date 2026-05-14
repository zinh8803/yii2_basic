<?php

namespace app\controllers;

use Exception;
use Yii;

class CalculateController extends BaseController
{
    public $modelClass = 'yii\\base\\Model';

    public function actionTotal()
    {
        $body = Yii::$app->request->bodyParams;
        $a = $body['a'] ?? null;
        $b = $body['b'] ?? null;
        if (!is_numeric($a) || !is_numeric($b)) {
            return $this->json(false, null, 'a and b must be numbers', 400);
        }
        $result = (float) $a + (float) $b;

        return $this->json(true, ['result' => $result], 'Success');

    }
    public function actionDivide()
    {
        try {
            $body = Yii::$app->request->bodyParams;
            $a = $body['a'] ?? null;
            $b = $body['b'] ?? null;

            if (!is_numeric($a) || !is_numeric($b)) {
                return $this->json(false, null, 'a and b must be numbers', 400);
            }

            if ($b === 0) {
                return $this->json(false, null, 'b cannot be zero', 400);
            }

            $result = (float) $a / (float) $b;

            return $this->json(true, ['result' => $result], 'Success');
        } catch (Exception $e) {
            return $this->json(false, null, 'Internal Server Error', 500);
        }
    }
    public function actionAverage()
    {
        $body = Yii::$app->request->bodyParams;
        $numbers = $body['numbers'] ?? [];

        if (!is_array($numbers)) {
            return $this->json(false, null, 'số liệu phải là một mảng', 400);
        }

        $flatNumbers = [];
        $error = null;
        $this->collectNumbers($numbers, $flatNumbers, $error);

        if ($error !== null) {
            return $this->json(false, null, $error, 400);
        }

        $sum = array_sum($flatNumbers);
        $count = count($flatNumbers);

        if ($count === 0) {
            return $this->json(false, null, 'Mảng số liệu trống.', 400);
        }

        $average = $sum / $count;

        return $this->json(true, ['average' => $average], 'Success');
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
