<?php
namespace app\models\forms\Brand;
use app\models\Brands;
use yii\base\Model;
class UpdateBrandForm extends Model
{
    public $id;
    public $name;
    public $slug;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [
                ['name'],
                'unique',
                'targetClass' => Brands::class,
                'targetAttribute' => 'name',
                'filter' => ['!=', 'id', $this->id],
            ],
            [['name'], 'required'],
            [
                ['slug'],
                'unique',
                'targetClass' => Brands::class,
                'targetAttribute' => 'slug',
                'filter' => ['!=', 'id', $this->id],
            ],
            [['status'], 'integer'],
            [['name', 'slug', 'description'], 'string', 'max' => 255],
        ];
    }
}
