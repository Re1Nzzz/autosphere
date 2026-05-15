<?php
/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<div class="as-auth-wrap">
    <div class="as-auth-box">
        <div style="text-align:center;margin-bottom:24px">
            <span style="font-size:2.5rem">⬡</span>
        </div>
        <h1 class="as-auth-box__title">С возвращением</h1>
        <p class="as-auth-box__sub">Войди в свой аккаунт AutoSphere</p>

        <?php $form = ActiveForm::begin([
                'id'          => 'loginForm',
                'options'     => ['class' => 'as-form'],
                'fieldConfig' => [
                        'template'     => '{label}{input}{error}',
                        'labelOptions' => ['class' => 'as-form-label'],
                        'inputOptions' => ['class' => 'as-form-input'],
                        'errorOptions' => ['class' => 'help-block-error'],
                ],
        ]) ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Логин или email']) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => '••••••••']) ?>
        <?= $form->field($model, 'rememberMe')->checkbox(['class' => '']) ?>

        <button type="submit" class="as-btn as-btn--primary as-btn--full">Войти</button>

        <?php ActiveForm::end() ?>

        <p class="as-auth-box__foot">
            Нет аккаунта? <a href="<?= Url::to(['/auth/register']) ?>">Зарегистрироваться</a>
        </p>
    </div>
</div>