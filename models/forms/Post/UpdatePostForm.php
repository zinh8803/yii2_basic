<?php

namespace app\models\forms\Post;

use app\models\Products;
use app\models\Tags;
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
    public $tag_ids;
    public $products;

    public function beforeValidate(): bool
    {
        if ($this->tag_ids !== null) {
            $this->tag_ids = $this->normalizeIdArray($this->tag_ids);
        }
        if ($this->products !== null) {
            $this->products = $this->normalizeIdArray($this->products);
        }

        return parent::beforeValidate();
    }

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'published_at'], 'integer'],
            [['excerpt', 'content'], 'string'],
            [['title', 'slug', 'meta_title', 'meta_description'], 'string', 'max' => 255],
            [['status', 'post_style'], 'string', 'max' => 50],
            [['tag_ids'], 'each', 'rule' => ['integer']],
            [['products'], 'each', 'rule' => ['integer']],
            [['tag_ids'], 'validateTagIds'],
            [['products'], 'validateProductIds'],
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

    public function validateTagIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, Tags::class, 'Invalid tag IDs: ');
    }

    public function validateProductIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, Products::class, 'Invalid product IDs: ');
    }

    private function validateExistingIds(string $attribute, string $modelClass, string $messagePrefix): void
    {
        if ($this->{$attribute} === null) {
            return;
        }

        $ids = $this->normalizeIdArray($this->{$attribute});
        if (empty($ids)) {
            return;
        }

        $existingIds = $modelClass::find()
            ->select('id')
            ->where(['id' => $ids])
            ->column();

        $missingIds = array_values(array_diff($ids, array_map('intval', $existingIds)));
        if ($missingIds !== []) {
            $this->addError($attribute, $messagePrefix . implode(', ', $missingIds));
        }
    }

    private function normalizeIdArray($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);

        $ids = [];
        foreach ($items as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }
}
