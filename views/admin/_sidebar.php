<?php
use yii\helpers\Url;
$cur = Yii::$app->controller->action->id;
?>
<div class="as-admin-sidebar">
    <div class="as-admin-sidebar__section">
        <span class="as-admin-sidebar__label">Управление</span>
        <a href="<?= Url::to(['/admin']) ?>" class="as-admin-link <?= $cur === 'index' ? 'active' : '' ?>">
            📊 Дашборд
        </a>
        <a href="<?= Url::to(['/admin/users']) ?>" class="as-admin-link <?= $cur === 'users' ? 'active' : '' ?>">
            👤 Пользователи
        </a>
        <a href="<?= Url::to(['/admin/parts']) ?>" class="as-admin-link <?= $cur === 'parts' ? 'active' : '' ?>">
            ⚙️ Запчасти
        </a>
        <a href="<?= Url::to(['/admin/moderate']) ?>" class="as-admin-link <?= $cur === 'moderate' ? 'active' : '' ?>">
            🛡 Модерация
        </a>
    </div>
    <div class="as-admin-sidebar__section">
        <span class="as-admin-sidebar__label">Сайт</span>
        <a href="<?= Url::to(['/gallery']) ?>" class="as-admin-link">🖼 Галерея</a>
        <a href="<?= Url::to(['/map']) ?>" class="as-admin-link">🗺 Карта</a>
        <a href="<?= Url::to(['/']) ?>" class="as-admin-link">🏠 На сайт</a>
    </div>
</div>