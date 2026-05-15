<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int    $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $avatar
 * @property string $bio
 * @property string $role
 * @property int    $status
 * @property int    $created_at
 * @property int    $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 0;

    const ROLE_USER      = 'user';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_ADMIN     = 'admin';

    public static function tableName(): string
    {
        return 'user';
    }

    /* ==================== IdentityInterface ==================== */

    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId(): int { return $this->id; }
    public function getAuthKey(): string { return $this->auth_key; }
    public function validateAuthKey($authKey): bool { return $this->auth_key === $authKey; }

    /* ==================== Password ==================== */

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /* ==================== Helpers ==================== */

    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isModerator(): bool  { return in_array($this->role, [self::ROLE_MODERATOR, self::ROLE_ADMIN]); }

    public function getAvatarLetter(): string
    {
        return mb_strtoupper(mb_substr($this->username, 0, 1));
    }

    /* ==================== Relations ==================== */

    public function getBuilds(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Build::class, ['user_id' => 'id']);
    }

    public function getPublishedBuilds(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Build::class, ['user_id' => 'id'])
            ->where(['is_published' => 1]);
    }

    /* ==================== Events ==================== */

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = time();
        if ($insert) { $this->created_at = $now; }
        $this->updated_at = $now;
        return true;
    }
}