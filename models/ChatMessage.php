<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ChatMessage extends ActiveRecord
{
    public static function tableName(): string { return 'chat_message'; }

    public function rules(): array
    {
        return [
            ['text', 'required', 'message' => 'Сообщение не может быть пустым'],
            ['text', 'string', 'max' => 500],
            [['user_id', 'is_deleted'], 'integer'],
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

    public static function getHistory(int $limit = 50): array
    {
        return static::find()
            ->with('user')
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    public static function getSince(int $lastId): array
    {
        return static::find()
            ->with('user')
            ->where(['>', 'id', $lastId])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    public function toClientArray(int $currentUserId = 0, bool $isMod = false): array
    {
        $user = $this->user;
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'username'      => $user ? $user->username : 'Unknown',
            'avatar_letter' => $user ? $user->getAvatarLetter() : '?',
            'role'          => $user ? $user->role : 'user',
            'text'          => $this->is_deleted ? '[Удалено]' : $this->text,
            'is_deleted'    => (bool)$this->is_deleted,
            'time'          => date('H:i', $this->created_at),
            'is_own'        => ($this->user_id === $currentUserId),
            'can_delete'    => ($isMod || $this->user_id === $currentUserId),
            'can_ban'       => $isMod,
        ];
    }
}