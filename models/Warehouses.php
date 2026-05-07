<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "warehouses".
 *
 * @property int $id
 * @property string $name
 * @property string|null $city
 * @property string $ward
 * @property string|null $detail
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property InventoryTransactions[] $inventoryTransactions
 * @property WarehouseUsers[] $warehouseUsers
 */
class Warehouses extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['city', 'detail'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'active'],
            [['name', 'ward', 'created_at', 'updated_at'], 'required'],
            [['city', 'detail'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'ward'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'city' => 'City',
            'ward' => 'Ward',
            'detail' => 'Detail',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[InventoryTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryTransactions()
    {
        return $this->hasMany(InventoryTransactions::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[WarehouseUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseUsers()
    {
        return $this->hasMany(WarehouseUsers::class, ['warehouse_id' => 'id']);
    }

}
