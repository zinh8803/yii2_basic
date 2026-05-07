<?php

namespace app\models;

use Yii;

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
 * @property Carts[] $carts
 * @property CouponUsages[] $couponUsages
 * @property Files[] $files
 * @property InventoryTransactions[] $inventoryTransactions
 * @property InventoryTransactions[] $inventoryTransactions0
 * @property Orders[] $orders
 * @property OtpEmails[] $otpEmails
 * @property Posts[] $posts
 * @property RefreshTokens[] $refreshTokens
 * @property Reviews[] $reviews
 * @property Roles $role
 * @property UserAddresses[] $userAddresses
 * @property WarehouseUsers[] $warehouseUsers
 */
class Users extends \yii\db\ActiveRecord
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
            [['role_id', 'username', 'email', 'password', 'phone_number', 'created_at', 'updated_at'], 'required'],
            [['role_id', 'created_at', 'updated_at', 'is_active'], 'integer'],
            [['username', 'email', 'password', 'phone_number'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Roles::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
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
        return $this->hasMany(Carts::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CouponUsages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsages::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[InventoryTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryTransactions()
    {
        return $this->hasMany(InventoryTransactions::class, ['approved_by' => 'id']);
    }

    /**
     * Gets query for [[InventoryTransactions0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryTransactions0()
    {
        return $this->hasMany(InventoryTransactions::class, ['created_by' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[OtpEmails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOtpEmails()
    {
        return $this->hasMany(OtpEmails::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[RefreshTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRefreshTokens()
    {
        return $this->hasMany(RefreshTokens::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Reviews::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Roles::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for [[UserAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddresses::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[WarehouseUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseUsers()
    {
        return $this->hasMany(WarehouseUsers::class, ['user_id' => 'id']);
    }

}
