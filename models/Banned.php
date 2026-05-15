<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $banned_by
 * @property string $reason
 * @property int    $created_at
 * @property int    $expires_at
 */
class Ban extends ActiveRecord
{
    public static function tableName(): string { return 'ban'; }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getBannedByUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'banned_by']);
    }
}