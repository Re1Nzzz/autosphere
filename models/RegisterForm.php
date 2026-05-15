<?php
namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public string $username = '';
    public string $email    = '';
    public string $password = '';
    public string $password_confirm = '';

    public function rules(): array
    {
        return [
            [['username','email','password','password_confirm'], 'required', 'message' => 'Обязательное поле'],
            ['username', 'string', 'min' => 3, 'max' => 32],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/', 'message' => 'Только буквы, цифры и _'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'Этот ник уже занят.'],
            ['email', 'email', 'message' => 'Некорректный email'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже используется.'],
            ['password', 'string', 'min' => 6, 'message' => 'Минимум 6 символов'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username'         => 'Имя пользователя',
            'email'            => 'Email',
            'password'         => 'Пароль',
            'password_confirm' => 'Подтверждение пароля',
        ];
    }

    public function register(): ?User
    {
        if (!$this->validate()) return null;

        $user = new User();
        $user->username = $this->username;
        $user->email    = $this->email;
        $user->role     = User::ROLE_USER;
        $user->status   = User::STATUS_ACTIVE;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}