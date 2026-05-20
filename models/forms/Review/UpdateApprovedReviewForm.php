<?php

namespace app\models\forms\Review;

use app\models\Products;
use app\models\Users;
use yii\base\Model;

class UpdateApprovedReviewForm extends Model
{
    public $id;
    public $is_approved;

    public function rules()
    {
        return [
          
            [['id'], 'integer'],
            [['is_approved'], 'boolean'],
        ];
    }

}
