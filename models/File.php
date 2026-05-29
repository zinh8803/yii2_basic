<?php

namespace app\models;

use app\behaviors\Timestamp;

class File extends base\File
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
