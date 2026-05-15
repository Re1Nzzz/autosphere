<?php
/** @var yii\web\View $this */
/** @var Exception $exception */
use yii\helpers\Url;
$code    = $exception->statusCode ?? 500;
$message = $exception->getMessage() ?: 'Что-то пошло не так';
?>
<div class="as-empty" style="min-height:calc(100vh - var(--nav-h));display:flex;flex-direction:column;align-items:center;justify-content:center">
    <div style="font-size:6rem;margin-bottom:16px;opacity:.5"><?= $code === 404 ? '🔍' : '⚠️' ?></div>
    <h1 class="as-section__title"><?= $code ?></h1>
    <p style="color:var(--text-2);font-size:1.1rem;margin:12px 0 32px;max-width:480px;text-align:center">
        <?= $code === 404 ? 'Страница не найдена. Возможно, она была удалена или перемещена.' : htmlspecialchars($message) ?>
    </p>
    <a href="<?= Url::to(['/']) ?>" class="as-btn as-btn--primary">На главную</a>
</div>