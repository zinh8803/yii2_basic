<?php

namespace app\models;

use app\models\base\BaseFile;
use app\behaviors\Timestamp;

class File extends BaseFile
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
