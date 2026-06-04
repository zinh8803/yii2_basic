<?php

namespace app\models\forms\Post;

use app\models\File;
use app\models\Resource;
use yii\base\Model;
use yii\web\UploadedFile;

class PostForm extends Model
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

    public $id;
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
    public $tag_ids;
    public $products;

    public function scenarios()
    {
        $attributes = [
            'title',
            'slug',
            'imageFile',
            'image_file_id',
            'image_resource_id',
            'excerpt',
            'content',
            'status',
            'post_style',
            'meta_title',
            'meta_description',
            'published_at',
            'tag_ids',
            'products',
        ];

        return [
            self::SCENARIO_CREATE => array_merge(['user_id'], $attributes),
            self::SCENARIO_UPDATE => array_merge(['id'], $attributes),
        ];
    }

    public function rules()
    {
        return [
            [['meta_title', 'meta_description', 'published_at'], 'default', 'value' => null],
            [['image_file_id', 'image_resource_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'draft', 'on' => self::SCENARIO_CREATE],
            [['post_style'], 'default', 'value' => 'standard', 'on' => self::SCENARIO_CREATE],
            [['user_id', 'title', 'excerpt', 'content'], 'required', 'on' => self::SCENARIO_CREATE],
            [['id'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['id', 'user_id', 'published_at', 'image_file_id', 'image_resource_id'], 'integer'],
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
                'targetClass' => File::class,
                'targetAttribute' => ['image_file_id' => 'id'],
            ],
            [
                ['image_resource_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Resource::class,
                'targetAttribute' => ['image_resource_id' => 'id'],
                'filter' => ['type' => 'image'],
            ],
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

}
