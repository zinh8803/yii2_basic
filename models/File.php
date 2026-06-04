<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseFile;

class File extends BaseFile
{
    public function fields()
    {
        return [
            'id',
            'path',
            'url',
            'mime_type',
            'width',
            'height',
        ];
    }

    public function extraFields()
    {
        return [
            'resources' => 'resources',
        ];
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
