<?php
namespace app\models;

use yii\db\ActiveRecord;

class ContactMessage extends ActiveRecord
{
    public static function tableName(): string { return 'contact_message'; }

    public function rules(): array
    {
        return [
            [['name', 'email', 'subject', 'message'], 'required'],
            ['name', 'string', 'max' => 128],
            ['email', 'email'],
            ['subject', 'string', 'max' => 255],
            ['message', 'string', 'max' => 3000],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name'    => 'Ваше имя',
            'email'   => 'Email',
            'subject' => 'Тема',
            'message' => 'Сообщение',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        if ($insert) $this->created_at = time();
        return true;
    }
}