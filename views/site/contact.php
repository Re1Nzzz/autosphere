<?php
/** @var yii\web\View $this */
/** @var app\models\ContactMessage $model */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="as-contact-wrap">
    <div class="as-contact-info">
        <h1 class="as-contact-info__title">Свяжитесь<br><span class="gradient-text">с нами</span></h1>
        <p class="as-contact-info__text">
            Есть вопросы, предложения или хочешь сотрудничать? Напиши нам — ответим в течение 24 часов.
        </p>
        <div class="as-socials">
            <a href="#" class="as-social-link">
                <span class="as-social-link__icon">✈️</span>
                <span>Telegram — @autosphere</span>
            </a>
            <a href="#" class="as-social-link">
                <span class="as-social-link__icon">📘</span>
                <span>VK — vk.com/autosphere</span>
            </a>
            <a href="#" class="as-social-link">
                <span class="as-social-link__icon">▶️</span>
                <span>YouTube — AutoSphere 3D</span>
            </a>
            <a href="mailto:admin@autosphere.local" class="as-social-link">
                <span class="as-social-link__icon">📧</span>
                <span>admin@autosphere.local</span>
            </a>
        </div>
    </div>

    <div class="as-card" style="padding:36px">
        <h2 style="font-family:var(--font-head);font-size:1.4rem;margin-bottom:24px">Написать сообщение</h2>

        <?php $form = ActiveForm::begin([
                'options'         => ['class' => 'as-form'],
                'fieldConfig'     => [
                        'template'        => '{label}{input}{error}',
                        'labelOptions'    => ['class' => 'as-form-label'],
                        'inputOptions'    => ['class' => 'as-form-input'],
                        'errorOptions'    => ['class' => 'help-block-error'],
                ],
        ]) ?>

        <?= $form->field($model, 'name')->textInput(['placeholder' => 'Ваше имя']) ?>
        <?= $form->field($model, 'email')->input('email', ['placeholder' => 'email@example.com']) ?>
        <?= $form->field($model, 'subject')->textInput(['placeholder' => 'Тема обращения']) ?>
        <?= $form->field($model, 'message')->textarea(['rows' => 5, 'placeholder' => 'Ваше сообщение...', 'class' => 'as-form-textarea']) ?>

        <button type="submit" class="as-btn as-btn--primary as-btn--full">Отправить сообщение</button>

        <?php ActiveForm::end() ?>
    </div>
</div>