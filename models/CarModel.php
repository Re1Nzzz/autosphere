<?php
namespace app\models;

use yii\db\ActiveRecord;

class CarModel extends ActiveRecord
{
    public static function tableName(): string { return 'car_model'; }

    public function getBrand(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CarBrand::class, ['id' => 'brand_id']);
    }

    public function getFullName(): string
    {
        return ($this->brand->name ?? '') . ' ' . $this->name;
    }

    public static function getDropdown(): array
    {
        $models = static::find()->with('brand')->orderBy('name')->all();
        $list   = [];
        foreach ($models as $m) {
            $list[$m->id] = ($m->brand->name ?? '') . ' ' . $m->name;
        }
        return $list;
    }
}