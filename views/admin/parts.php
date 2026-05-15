<?php
/** @var yii\web\View $this */
/** @var app\models\CarPart[] $parts */
/** @var app\models\CarPart $editPart */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\CarPart;
?>
<div class="as-admin-layout">
    <?= $this->render('_sidebar') ?>
    <div class="as-admin-content">
        <div class="flex-between mb-32">
            <h1 style="font-family:var(--font-head);font-size:1.8rem">Запчасти конструктора</h1>
            <button class="as-btn as-btn--primary" onclick="openModal('partModal')">+ Добавить запчасть</button>
        </div>

        <!-- Таблица -->
        <div class="as-table__wrap">
            <table class="as-table">
                <thead>
                <tr>
                    <th>ID</th><th>Категория</th><th>Название</th>
                    <th>Скорость</th><th>Управление</th><th>Вес</th>
                    <th>Цена</th><th>Активна</th><th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($parts as $p): ?>
                    <tr>
                        <td style="color:var(--text-3)"><?= $p->id ?></td>
                        <td>
                            <span class="as-badge as-badge--blue">
                                <?= $p->getCategoryIcon() ?> <?= $p->getCategoryLabel() ?>
                            </span>
                        </td>
                        <td><?= Html::encode($p->name) ?></td>
                        <td><?= $p->stat_speed ?></td>
                        <td><?= $p->stat_handling ?></td>
                        <td><?= $p->stat_weight ?></td>
                        <td><?= number_format($p->price, 0, '.', ' ') ?> ₽</td>
                        <td>
                            <?php if ($p->is_active): ?>
                                <span class="as-badge as-badge--green">Да</span>
                            <?php else: ?>
                                <span class="as-badge as-badge--red">Нет</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap">
                            <a href="<?= Url::to(['/admin/parts', 'edit' => $p->id]) ?>"
                               class="as-btn as-btn--ghost as-btn--sm">✏</a>
                            <a href="<?= Url::to(['/admin/part-delete/' . $p->id]) ?>"
                               class="as-btn as-btn--ghost as-btn--sm" style="color:var(--accent-red)"
                               onclick="return confirm('Удалить запчасть?')">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модалка добавления/редактирования -->
<div class="as-modal-overlay <?= $editPart->id ? 'open' : '' ?>" id="partModal">
    <div class="as-modal">
        <div class="as-modal__head">
            <span class="as-modal__title"><?= $editPart->id ? 'Редактировать' : 'Добавить' ?> запчасть</span>
            <button class="as-modal__close" onclick="closeModal('partModal')">×</button>
        </div>

        <?php $form = ActiveForm::begin([
            'action'  => Url::to(['/admin/part-save']),
            'options' => ['class' => 'as-form'],
            'fieldConfig' => [
                'template'     => '{label}{input}{error}',
                'labelOptions' => ['class' => 'as-form-label'],
                'inputOptions' => ['class' => 'as-form-input'],
                'errorOptions' => ['class' => 'help-block-error'],
            ],
        ]) ?>
        <?= Html::hiddenInput('id', $editPart->id) ?>

        <?= $form->field($editPart, 'category')->dropDownList(CarPart::CATEGORIES, ['class' => 'as-form-input']) ?>
        <?= $form->field($editPart, 'name')->textInput(['placeholder' => 'Street Kit...']) ?>
        <?= $form->field($editPart, 'description')->textarea(['rows' => 2, 'class' => 'as-form-textarea']) ?>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
            <?= $form->field($editPart, 'stat_speed')->textInput(['type' => 'number', 'min' => 0, 'max' => 20, 'placeholder' => '0']) ?>
            <?= $form->field($editPart, 'stat_handling')->textInput(['type' => 'number', 'min' => 0, 'max' => 20, 'placeholder' => '0']) ?>
            <?= $form->field($editPart, 'stat_weight')->textInput(['type' => 'number', 'min' => 0, 'max' => 10, 'placeholder' => '0']) ?>
        </div>

        <?= $form->field($editPart, 'price')->textInput(['type' => 'number', 'min' => 0, 'step' => '1000', 'placeholder' => '0']) ?>
        <?= $form->field($editPart, 'color_hex')->textInput(['placeholder' => '#6366f1']) ?>
        <?= $form->field($editPart, 'is_active')->checkbox() ?>

        <button type="submit" class="as-btn as-btn--primary as-btn--full">Сохранить</button>
        <?php ActiveForm::end() ?>
    </div>
</div>