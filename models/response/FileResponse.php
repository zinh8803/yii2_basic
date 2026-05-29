<?php

namespace app\models\response;
use app\models\File;

class FileResponse extends File
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
}
