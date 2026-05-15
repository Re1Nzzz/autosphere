<?php
/** @var yii\web\View $this */
/** @var app\models\RegisterForm $model */
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="as-auth-wrap">
    <div class="as-auth-box">
        <div style="text-align:center;margin-bottom:24px">
            <span style="font-size:2.5rem">⬡</span>
        </div>
        <h1 class="as-auth-box__title">Присоединяйся</h1>
        <p class="as-auth-box__sub">Создай аккаунт и собирай свою мечту</p>

        <?php $form = ActiveForm::begin([
            'id'          => 'registerForm',
            'options'     => ['class' => 'as-form'],
            'fieldConfig' => [
                'template'     => '{label}{input}{error}',
                'labelOptions' => ['class' => 'as-form-label'],
                'inputOptions' => ['class' => 'as-form-input'],
                'errorOptions' => ['class' => 'help-block-error'],
            ],
        ]) ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'racer_2025']) ?>
        <?= $form->field($model, 'email')->input('email', ['placeholder' => 'email@example.com']) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>
        <?= $form->field($model, 'password_confirm')->passwordInput(['placeholder' => 'Повтори пароль']) ?>

        <button type="submit" class="as-btn as-btn--primary as-btn--full">Создать аккаунт</button>

        <?php ActiveForm::end() ?>

        <p class="as-auth-box__foot">
            Уже есть аккаунт? <a href="<?= Url::to(['/auth/login']) ?>">Войти</a>
        </p>
    </div>
</div>