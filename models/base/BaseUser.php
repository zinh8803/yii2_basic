<?php

namespace app\models\base;

use app\models\Cart;
use app\models\CouponUsage;
use app\models\File;
use app\models\InventoryTransaction;
use app\models\Order;
use app\models\OtpEmail;
use app\models\Post;
use app\models\RefreshToken;
use app\models\Review;
use app\models\UserAddress;
use app\models\WarehouseUser;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property int $role_id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $phone_number
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_active
 *
 * @property Cart[] $carts
 * @property CouponUsage[] $couponUsages
 * @property File[] $files
 * @property InventoryTransaction[] $inventoryTransactions
 * @property InventoryTransaction[] $inventoryTransactions0
 * @property Order[] $orders
 * @property OtpEmail[] $otpEmails
 * @property Post[] $posts
 * @property RefreshToken[] $refreshTokens
 * @property Review[] $reviews
 * @property UserAddress[] $userAddresses
 * @property WarehouseUser[] $warehouseUsers
 */
class BaseUser extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_active'], 'default', 'value' => 1],
            [['username', 'email', 'password', 'phone_number'], 'required'],
            [['created_at', 'updated_at', 'is_active'], 'integer'],
            [['username', 'email', 'password', 'phone_number'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['auth_key'], 'string', 'max' => 64],
            [['auth_key'], 'unique'],
            [['access_token'], 'string', 'max' => 255],
            [['access_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'phone_number' => 'Phone Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * Gets query for [[Carts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarts()
    {
        return $this->hasMany(Cart::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CouponUsages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsage::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[InventoryTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, ['approved_by' => 'id']);
    }

    /**
     * Gets query for [[InventoryTransactions0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryTransactions0()
    {
        return $this->hasMany(InventoryTransaction::class, ['created_by' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[OtpEmails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOtpEmails()
    {
        return $this->hasMany(OtpEmail::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[RefreshTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRefreshTokens()
    {
        return $this->hasMany(RefreshToken::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::class, ['user_id' => 'id']);
    }


    /**
     * Gets query for [[UserAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddress::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[WarehouseUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseUsers()
    {
        return $this->hasMany(WarehouseUser::class, ['user_id' => 'id']);
    }

}
