<?php
/** @var yii\web\View $this */
/** @var app\models\ContactMessage[] $unreadMessages */
/** @var app\models\Build[] $unpublishedBuilds */
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="as-admin-layout">
    <?= $this->render('_sidebar') ?>
    <div class="as-admin-content">
        <div class="flex-between mb-32">
            <h1 style="font-family:var(--font-head);font-size:1.8rem">Модерация</h1>
            <?php if ($unreadMessages): ?>
                <a href="<?= Url::to(['/admin/moderate', 'markRead' => 1]) ?>"
                   class="as-btn as-btn--ghost as-btn--sm">
                    ✓ Отметить все как прочитанные
                </a>
            <?php endif; ?>
        </div>

        <!-- Сообщения с формы -->
        <div class="mb-32">
            <h2 style="font-family:var(--font-head);font-size:1.3rem;margin-bottom:16px">
                ✉️ Сообщения с формы
                <?php if ($unreadMessages): ?>
                    <span class="as-badge as-badge--red" style="margin-left:8px"><?= count($unreadMessages) ?> новых</span>
                <?php endif; ?>
            </h2>

            <?php if ($unreadMessages): ?>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <?php foreach ($unreadMessages as $m): ?>
                        <div class="as-card" style="padding:20px">
                            <div class="flex-between mb-8">
                                <div>
                                    <strong><?= Html::encode($m->name) ?></strong>
                                    <span style="color:var(--text-3);font-size:.8rem;margin-left:8px"><?= Html::encode($m->email) ?></span>
                                </div>
                                <span style="color:var(--text-3);font-size:.8rem"><?= date('d.m.Y H:i', $m->created_at) ?></span>
                            </div>
                            <div style="color:var(--accent);font-size:.9rem;margin-bottom:8px">
                                📋 <?= Html::encode($m->subject) ?>
                            </div>
                            <p style="color:var(--text-2);font-size:.875rem;line-height:1.6">
                                <?= nl2br(Html::encode($m->message)) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="as-empty" style="padding:40px">
                    <div class="as-empty__icon">📭</div>
                    <p class="as-empty__text">Новых сообщений нет</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Черновики сборок -->
        <div>
            <h2 style="font-family:var(--font-head);font-size:1.3rem;margin-bottom:16px">
                🚗 Неопубликованные сборки (<?= count($unpublishedBuilds) ?>)
            </h2>

            <?php if ($unpublishedBuilds): ?>
                <div class="as-table__wrap">
                    <table class="as-table">
                        <thead>
                        <tr><th>Сборка</th><th>Автор</th><th>Создана</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($unpublishedBuilds as $b): ?>
                            <tr>
                                <td><?= Html::encode($b->name) ?></td>
                                <td><?= Html::encode($b->user->username ?? '—') ?></td>
                                <td style="color:var(--text-3);font-size:.8rem"><?= date('d.m.Y', $b->created_at) ?></td>
                                <td>
                                    <a href="<?= Url::to(['/constructor', 'build' => $b->id]) ?>"
                                       class="as-btn as-btn--ghost as-btn--sm">👁 Просмотр</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--text-3)">Все сборки опубликованы или нет новых</p>
            <?php endif; ?>
        </div>
    </div>
</div>