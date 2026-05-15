<?php

namespace app\models\forms\PostProduct;

use app\models\Posts;
use app\models\Products;
use yii\base\Model;

class CreatePostProductForm extends Model
{
    public $post_id;
    public $product_id;
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['post_id', 'product_id'], 'required'],
            [["post_id", "product_id"], 'integer'],
            ["post_id", 'exist', 'skipOnError' => true, 'targetClass' => Posts::class, 'targetAttribute' => ['post_id' => 'id']],
            ["product_id", 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
        ]);
    }
}
