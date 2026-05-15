<?php
namespace app\models;

use yii\db\ActiveRecord;

class Like extends ActiveRecord
{
    public static function tableName(): string { return 'like'; }

    public function getBuild(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Build::class, ['id' => 'build_id']);
    }

    public static function toggle(int $buildId, int $userId): array
    {
        $existing = static::findOne(['build_id' => $buildId, 'user_id' => $userId]);

        if ($existing) {
            $existing->delete();
            Build::updateAllCounters(['likes_count' => -1], ['id' => $buildId]);
            $liked = false;
        } else {
            $like             = new static();
            $like->build_id   = $buildId;
            $like->user_id    = $userId;
            $like->created_at = time();
            $like->save();
            Build::updateAllCounters(['likes_count' => 1], ['id' => $buildId]);
            $liked = true;
        }

        $count = (int)(Build::findOne($buildId)->likes_count ?? 0);
        return compact('liked', 'count');
    }
}