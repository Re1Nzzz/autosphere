<?php
namespace app\models;

use yii\db\ActiveRecord;

class CarBrand extends ActiveRecord
{
    public static function tableName(): string { return 'car_brand'; }

    public function getModels(): \yii\db\ActiveQuery
    {
        return $this->hasMany(CarModel::class, ['brand_id' => 'id']);
    }

    public static function getList(): array
    {
        return static::find()->orderBy('name')->select(['id','name'])->asArray()->all();
    }
}