<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $category
 * @property string $name
 * @property string $description
 * @property string $thumbnail
 * @property string $model_file
 * @property string $color_hex
 * @property int    $stat_speed
 * @property int    $stat_handling
 * @property int    $stat_weight
 * @property float  $price
 * @property int    $is_active
 * @property int    $sort_order
 */
class CarPart extends ActiveRecord
{
    const CATEGORIES = [
        'body'       => 'Кузов',
        'wheels'     => 'Диски',
        'suspension' => 'Подвеска',
        'spoiler'    => 'Спойлер',
        'color'      => 'Цвет',
        'interior'   => 'Интерьер',
    ];

    const CATEGORY_ICONS = [
        'body'       => '🚗',
        'wheels'     => '⭕',
        'suspension' => '🔧',
        'spoiler'    => '🔝',
        'color'      => '🎨',
        'interior'   => '💺',
    ];

    public static function tableName(): string { return 'car_part'; }

    public function rules(): array
    {
        return [
            [['category','name'], 'required'],
            [['name','thumbnail','model_file','color_hex'], 'string', 'max' => 255],
            ['description', 'string'],
            ['category', 'in', 'range' => array_keys(self::CATEGORIES)],
            [['stat_speed','stat_handling','stat_weight','is_active','sort_order'], 'integer'],
            ['price', 'number'],
            [['stat_speed','stat_handling','stat_weight'], 'default', 'value' => 0],
            ['is_active', 'default', 'value' => 1],
            ['sort_order', 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'         => 'Название',
            'category'     => 'Категория',
            'description'  => 'Описание',
            'stat_speed'   => 'Скорость',
            'stat_handling'=> 'Управляемость',
            'stat_weight'  => 'Вес (бонус)',
            'price'        => 'Цена (₽)',
            'is_active'    => 'Активна',
        ];
    }

    public function getCategoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getCategoryIcon(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? '⚙️';
    }

    public static function getByCategory(string $category): array
    {
        return static::find()
            ->where(['category' => $category, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    public static function getAllGrouped(): array
    {
        $parts = static::find()->where(['is_active' => 1])->orderBy('sort_order')->all();
        $grouped = [];
        foreach ($parts as $p) {
            $grouped[$p->category][] = $p;
        }
        return $grouped;
    }
}