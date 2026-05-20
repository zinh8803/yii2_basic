<?php

namespace app\models\response\File;
use app\models\Files;

class FileResponse extends Files
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
