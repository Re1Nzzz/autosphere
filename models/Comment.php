<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function tableName(): string { return 'comment'; }

    public function rules(): array
    {
        return [
            ['text', 'required', 'message' => 'Комментарий не может быть пустым'],
            ['text', 'string', 'max' => 1000],
            [['build_id', 'user_id', 'is_deleted'], 'integer'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        if ($insert) {
            $this->created_at = time();
            $this->user_id    = Yii::$app->user->id;
            $this->is_deleted = 0;
        }
        return true;
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}