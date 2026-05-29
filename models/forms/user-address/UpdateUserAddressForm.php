<?php

namespace app\models\forms\UserAddress;

use app\models\Users;
use yii\base\Model;

class UpdateUserAddressForm extends Model
{
    public $id;
    public $user_id;
    public $city;
    public $ward;
    public $detail_address;
    public $phone_number;
    public $name_address;

    public function rules()
    {
        return [
            [['user_id', 'city', 'ward', 'detail_address', 'phone_number', 'name_address'], 'required'],
            [['user_id'], 'integer'],
            [['city', 'ward'], 'string', 'max' => 100],
            [['detail_address', 'name_address'], 'string', 'max' => 255],
            [['phone_number'], 'string', 'max' => 20],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
}
