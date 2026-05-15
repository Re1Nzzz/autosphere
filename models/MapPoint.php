<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MapPoint extends ActiveRecord
{
    const TYPES = [
        'event'   => 'Событие',
        'service' => 'СТО',
        'shop'    => 'Магазин',
        'photo'   => 'Фотосет',
    ];

    const TYPE_ICONS = [
        'event'   => '🎉',
        'service' => '🔧',
        'shop'    => '🛒',
        'photo'   => '📸',
    ];

    const TYPE_COLORS = [
        'event'   => '#6366f1',
        'service' => '#10b981',
        'shop'    => '#f59e0b',
        'photo'   => '#06b6d4',
    ];

    public static function tableName(): string { return 'map_point'; }

    public function rules(): array
    {
        return [
            [['type', 'name', 'lat', 'lng'], 'required'],
            ['name', 'string', 'max' => 128],
            ['description', 'string'],
            ['type', 'in', 'range' => array_keys(self::TYPES)],
            [['lat', 'lng'], 'number'],
            [['user_id', 'is_approved'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'type'        => 'Тип',
            'name'        => 'Название',
            'description' => 'Описание',
            'lat'         => 'Широта',
            'lng'         => 'Долгота',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        if ($insert) {
            $this->created_at  = time();
            $this->user_id     = Yii::$app->user->id;
            $this->is_approved = 0;
        }
        return true;
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTypeLabel(): string  { return self::TYPES[$this->type]       ?? $this->type; }
    public function getTypeIcon(): string   { return self::TYPE_ICONS[$this->type]  ?? '📍'; }
    public function getTypeColor(): string  { return self::TYPE_COLORS[$this->type] ?? '#ffffff'; }

    public static function getApproved(?string $type = null): array
    {
        $query = static::find()->with('user')->where(['is_approved' => 1]);
        if ($type) $query->andWhere(['type' => $type]);
        return $query->all();
    }

    public function toMapArray(): array
    {
        return [
            'id'    => $this->id,
            'type'  => $this->type,
            'label' => $this->getTypeLabel(),
            'icon'  => $this->getTypeIcon(),
            'color' => $this->getTypeColor(),
            'name'  => $this->name,
            'desc'  => $this->description,
            'lat'   => (float)$this->lat,
            'lng'   => (float)$this->lng,
            'user'  => $this->user->username ?? '',
        ];
    }
}