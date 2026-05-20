<?php

namespace app\models\forms\Review;

use app\models\Products;
use app\models\Users;
use yii\base\Model;

class CreateReviewForm extends Model
{
    public $product_id;
    public $user_id;
    public $rating;
    public $comment;

    public function rules()
    {
        return [
            [['product_id', 'user_id', 'rating'], 'required'],
            [['product_id', 'user_id'], 'integer'],
            [['rating'], 'number', 'min' => 1, 'max' => 5],
            [['comment'], 'string'],
            [
                ['user_id'],
                'exist',
                'targetClass' => Users::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],

            [
                ['product_id'],
                'exist',
                'targetClass' => Products::class,
                'targetAttribute' => ['product_id' => 'id'],
            ],
        ];
    }
}
