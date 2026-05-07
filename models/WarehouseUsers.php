<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "warehouse_users".
 *
 * @property int $id
 * @property int $warehouse_id
 * @property int $user_id
 * @property string $role
 * @property int $can_import
 * @property int $can_export
 * @property int $can_transfer
 * @property int $can_adjust_stock
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 * @property Warehouses $warehouse
 */
class WarehouseUsers extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouse_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role'], 'default', 'value' => 'staff'],
            [['can_adjust_stock'], 'default', 'value' => 0],
            [['warehouse_id', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['warehouse_id', 'user_id', 'can_import', 'can_export', 'can_transfer', 'can_adjust_stock', 'created_at', 'updated_at'], 'integer'],
            [['role'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouses::class, 'targetAttribute' => ['warehouse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_id' => 'Warehouse ID',
            'user_id' => 'User ID',
            'role' => 'Role',
            'can_import' => 'Can Import',
            'can_export' => 'Can Export',
            'can_transfer' => 'Can Transfer',
            'can_adjust_stock' => 'Can Adjust Stock',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouses::class, ['id' => 'warehouse_id']);
    }

}
