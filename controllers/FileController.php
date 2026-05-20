<?php

namespace app\controllers;

use app\models\response\File\FileResponse;

class FileController extends BaseController
{
    public $modelClass = 'app\models\Files';

    public function actionIndex($resource_type = null)
    {
        $query = FileResponse::find()
            ->alias('f')
            ->orderBy(['f.created_at' => SORT_DESC]);

        if ($resource_type !== null) {
            $query->joinWith(['resources'])
                ->andWhere([
                    'resources.resource_type' => $resource_type
                ]);
        }

        $data = $this->paginate($query);

        return $this->json(true, $data, 'Get list file successfully');
    }
}
