<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "inventory_transactions".
 *
 * @property int $id
 * @property string $type
 * @property int $warehouse_id
 * @property int $created_by
 * @property int|null $approved_by
 * @property string $status
 * @property string|null $note
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $approvedBy
 * @property Users $createdBy
 * @property Warehouses $warehouse
 */
class InventoryTransactions extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventory_transactions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['approved_by', 'note'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['type', 'warehouse_id', 'created_by', 'created_at', 'updated_at'], 'required'],
            [['warehouse_id', 'created_by', 'approved_by', 'created_at', 'updated_at'], 'integer'],
            [['note'], 'string'],
            [['type', 'status'], 'string', 'max' => 50],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['approved_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['created_by' => 'id']],
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
            'type' => 'Type',
            'warehouse_id' => 'Warehouse ID',
            'created_by' => 'Created By',
            'approved_by' => 'Approved By',
            'status' => 'Status',
            'note' => 'Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ApprovedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprovedBy()
    {
        return $this->hasOne(Users::class, ['id' => 'approved_by']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Users::class, ['id' => 'created_by']);
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
