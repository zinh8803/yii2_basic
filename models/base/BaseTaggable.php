<?php

namespace app\models\base;

use app\models\Post;
use app\models\Tag;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "taggables".
 *
 * @property int $id
 * @property int $tag_id
 * @property int|null $post_id
 * @property string $type
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Post $post
 * @property Tag $tag
 */
class BaseTaggable extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'taggables';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id'], 'default', 'value' => null],
            [['tag_id', 'type'], 'required'],
            [['tag_id', 'post_id'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_id' => 'Tag ID',
            'post_id' => 'Post ID',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

}
