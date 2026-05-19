<?php

namespace app\models\forms\Post;

use yii\base\Model;

class UpdatePostStatusForm extends Model
{
    public $id;
    public $status;

    public function rules()
    {
        return [
            [['id', 'status'], 'required'],
            [['id'], 'integer'],
            [['status'], 'string', 'max' => 50],
        ];
    }
}
