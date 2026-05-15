<?php

namespace app\models\forms\Post;

use app\models\Users;
use yii\base\Model;
use yii\web\UploadedFile;

class UpdatePostForm extends Model
{
    public $id;
    public $user_id;
    public $title;
    public $slug;
    /** @var UploadedFile|null */
    public $imageFile;
    public $excerpt;
    public $content;
    public $status;
    public $post_style;
    public $meta_title;
    public $meta_description;
    public $published_at;
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'published_at'], 'integer'],
            [['excerpt', 'content'], 'string'],
            [['title', 'slug', 'meta_title', 'meta_description'], 'string', 'max' => 255],
            [['status', 'post_style'], 'string', 'max' => 50],
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 5 * 1024 * 1024,
            ],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
}
