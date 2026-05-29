<?php

namespace app\models\base;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "posts".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property string $status
 * @property string $post_style
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int|null $published_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property PostProduct[] $postProducts
 * @property Taggable[] $taggables
 * @property User $user
 */
class Post extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meta_title', 'meta_description', 'published_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'draft'],
            [['post_style'], 'default', 'value' => 'standard'],
            [['user_id', 'title', 'slug', 'excerpt', 'content'], 'required'],
            [['user_id', 'published_at'], 'integer'],
            [['excerpt', 'content'], 'string'],
            [['title', 'slug', 'meta_title', 'meta_description'], 'string', 'max' => 255],
            [['status', 'post_style'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'status' => 'Status',
            'post_style' => 'Post Style',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'published_at' => 'Published At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[PostProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostProducts()
    {
        return $this->hasMany(PostProduct::class, ['post_id' => 'id']);
    }

    /**
     * Gets resources (images) for this post.
     * @return \yii\db\ActiveQuery
     */

    /**
     * Gets query for [[Taggables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaggables()
    {
        return $this->hasMany(Taggable::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
