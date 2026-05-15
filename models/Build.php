<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $car_model_id
 * @property string $name
 * @property string $config_json
 * @property string $screenshot
 * @property int    $is_published
 * @property int    $likes_count
 * @property int    $views_count
 * @property int    $created_at
 * @property int    $updated_at
 *
 * @property User     $user
 * @property CarModel $carModel
 * @property Comment[] $comments
 */
class Build extends ActiveRecord
{
    public static function tableName(): string { return 'build'; }

    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 128],
            [['user_id','car_model_id','is_published','likes_count','views_count'], 'integer'],
            ['config_json', 'string'],
            ['screenshot', 'string', 'max' => 255],
            ['is_published', 'default', 'value' => 0],
            ['likes_count', 'default', 'value' => 0],
            ['views_count', 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'         => 'Название сборки',
            'is_published' => 'Опубликовано',
            'likes_count'  => 'Лайки',
            'views_count'  => 'Просмотры',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = time();
        if ($insert) {
            $this->created_at = $now;
            $this->user_id    = Yii::$app->user->id;
        }
        $this->updated_at = $now;
        return true;
    }

    /* ==================== Relations ==================== */

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCarModel(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CarModel::class, ['id' => 'car_model_id']);
    }

    public function getComments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Comment::class, ['build_id' => 'id'])
            ->where(['is_deleted' => 0])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    public function getLikes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Like::class, ['build_id' => 'id']);
    }

    /* ==================== Helpers ==================== */

    public function getConfig(): array
    {
        return $this->config_json ? (json_decode($this->config_json, true) ?? []) : [];
    }

    public function isLikedBy(int $userId): bool
    {
        return Like::find()->where(['build_id' => $this->id, 'user_id' => $userId])->exists();
    }

    public function incrementViews(): void
    {
        static::updateAllCounters(['views_count' => 1], ['id' => $this->id]);
    }

    public function computeStats(): array
    {
        $config = $this->getConfig();
        $stats = ['speed' => 50, 'handling' => 50, 'weight' => 50];

        foreach ($config as $category => $partId) {
            if ($category === 'color') continue;
            $part = CarPart::findOne((int)$partId);
            if ($part) {
                $stats['speed']    = min(99, $stats['speed']    + $part->stat_speed);
                $stats['handling'] = min(99, $stats['handling'] + $part->stat_handling);
                $stats['weight']   = min(99, $stats['weight']   - $part->stat_weight);
            }
        }

        return $stats;
    }

    /* ==================== Static helpers ==================== */

    public static function getPublished(array $filters = [], int $page = 1, int $pageSize = 12): array
    {
        $query = static::find()
            ->with(['user'])
            ->where(['is_published' => 1]);

        if (!empty($filters['brand'])) {
            $query->joinWith('carModel.brand')
                ->andWhere(['car_brand.name' => $filters['brand']]);
        }
        if (!empty($filters['q'])) {
            $query->andWhere(['like', 'build.name', $filters['q']]);
        }

        $sort = $filters['sort'] ?? 'latest';
        if ($sort === 'popular') {
            $query->orderBy(['likes_count' => SORT_DESC, 'created_at' => SORT_DESC]);
        } else {
            $query->orderBy(['created_at' => SORT_DESC]);
        }

        $total = $query->count();
        $items = $query->offset(($page - 1) * $pageSize)->limit($pageSize)->all();

        return compact('items', 'total', 'page', 'pageSize');
    }
}