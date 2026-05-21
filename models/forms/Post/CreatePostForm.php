<?php

namespace app\models\forms\Post;

use app\models\Files;
use app\models\Resources;
use app\models\Users;
use yii\base\Model;
use yii\web\UploadedFile;

class CreatePostForm extends Model
{
    public $user_id;
    public $title;
    public $slug;
    /** @var UploadedFile|null */
    public $imageFile;
    public $image_file_id;
    public $image_resource_id;
    public $excerpt;
    public $content;
    public $status;
    public $post_style;
    public $meta_title;
    public $meta_description;
    public $published_at;
    public $tag_ids = [];

    public $products = [];

    public function rules()
    {
        return [
            [['meta_title', 'meta_description', 'published_at'], 'default', 'value' => null],
            [['image_file_id', 'image_resource_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'draft'],
            [['post_style'], 'default', 'value' => 'standard'],
            [['user_id', 'title', 'excerpt', 'content'], 'required'],
            [['user_id', 'published_at', 'image_file_id', 'image_resource_id'], 'integer'],
            [['excerpt', 'content'], 'string'],
            [['title', 'slug', 'meta_title', 'meta_description'], 'string', 'max' => 255],
            [['status', 'post_style'], 'string', 'max' => 50],
            [['tag_ids'], 'each', 'rule' => ['integer']],
            [['products'], 'each', 'rule' => ['integer']],
            [['imageFile'], 'validateSingleImageSource'],
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 5 * 1024 * 1024,
            ],
            [
                ['image_file_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Files::class,
                'targetAttribute' => ['image_file_id' => 'id'],
            ],
            [
                ['image_resource_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Resources::class,
                'targetAttribute' => ['image_resource_id' => 'id'],
                'filter' => ['type' => 'image'],
            ],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function validateSingleImageSource(): void
    {
        $sources = array_filter([
            $this->imageFile instanceof UploadedFile,
            !empty($this->image_file_id),
            !empty($this->image_resource_id),
        ]);
        if (count($sources) > 1) {
            $this->addError('imageFile', 'Choose only one image source.');
        }
    }

    /**
     * Create and save a new Posts model from this form
     * @return \app\models\Posts|null
     */
}
