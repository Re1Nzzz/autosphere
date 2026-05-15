<?php
/** @var yii\web\View $this */
/** @var app\models\Build[] $builds */
/** @var array $stats */
/** @var array $recentLikes */
use yii\helpers\Html;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
?>
<div class="as-section">

    <!-- Шапка профиля -->
    <div class="as-garage-header">
        <div class="as-garage-avatar">
            <?php if ($user->avatar): ?>
                <img src="<?= Html::encode($user->avatar) ?>" alt="">
            <?php else: ?>
                <?= $user->getAvatarLetter() ?>
            <?php endif; ?>
        </div>
        <div style="flex:1">
            <h1 style="font-family:var(--font-head);font-size:1.8rem;font-weight:700;margin-bottom:4px">
                <?= Html::encode($user->username) ?>
            </h1>
            <p style="color:var(--text-3);font-size:.875rem"><?= Html::encode($user->email) ?></p>
            <?php if ($user->bio): ?>
                <p style="color:var(--text-2);font-size:.875rem;margin-top:6px"><?= Html::encode($user->bio) ?></p>
            <?php endif; ?>
        </div>
        <div class="as-garage-stats">
            <div class="as-garage-stat">
                <span class="as-garage-stat__val"><?= $stats['total'] ?></span>
                <span class="as-garage-stat__label">Сборок</span>
            </div>
            <div class="as-garage-stat">
                <span class="as-garage-stat__val"><?= $stats['published'] ?></span>
                <span class="as-garage-stat__label">В галерее</span>
            </div>
            <div class="as-garage-stat">
                <span class="as-garage-stat__val"><?= $stats['likes'] ?></span>
                <span class="as-garage-stat__label">Лайков</span>
            </div>
            <div class="as-garage-stat">
                <span class="as-garage-stat__val"><?= number_format($stats['views']) ?></span>
                <span class="as-garage-stat__label">Просмотров</span>
            </div>
        </div>
    </div>

    <!-- Кнопка новой сборки -->
    <div class="flex-between mb-32">
        <h2 style="font-family:var(--font-head);font-size:1.5rem">Мои сборки</h2>
        <a href="<?= Url::to(['/constructor']) ?>" class="as-btn as-btn--primary">+ Новая сборка</a>
    </div>

    <!-- Сборки -->
    <?php if ($builds): ?>
        <div class="as-grid as-grid--3" style="margin-bottom:48px">
            <?php foreach ($builds as $build): ?>
                <div class="as-card">
                    <div class="as-build-card__screenshot">
                        <?php if ($build->screenshot): ?>
                            <img src="<?= Html::encode($build->screenshot) ?>" alt="">
                        <?php else: ?>
                            <span>🚗</span>
                        <?php endif; ?>
                        <div class="as-build-card__actions">
                            <span class="as-like-btn">❤ <?= $build->likes_count ?></span>
                        </div>
                    </div>

                    <div class="as-card__body">
                        <div class="as-card__title"><?= Html::encode($build->name) ?></div>
                        <div class="as-card__meta mt-8">
                            <?php if ($build->is_published): ?>
                                <span class="as-badge as-badge--green">✓ Опубликовано</span>
                            <?php else: ?>
                                <span class="as-badge as-badge--orange">⏸ Черновик</span>
                            <?php endif; ?>
                            <span style="color:var(--text-3);font-size:.75rem">👁 <?= $build->views_count ?></span>
                        </div>
                    </div>

                    <div class="as-card__foot" style="gap:6px;flex-wrap:wrap">
                        <a href="<?= Url::to(['/constructor', 'build' => $build->id]) ?>"
                           class="as-btn as-btn--ghost as-btn--sm">✏</a>

                        <?php if ($build->is_published): ?>
                            <a href="<?= Url::to(['/gallery/' . $build->id]) ?>"
                               class="as-btn as-btn--ghost as-btn--sm">👁</a>
                        <?php endif; ?>

                        <a href="<?= Url::to(['/garage/publish/' . $build->id]) ?>"
                           class="as-btn as-btn--ghost as-btn--sm"
                           title="<?= $build->is_published ? 'Снять с публикации' : 'Опубликовать' ?>">
                            <?= $build->is_published ? '📤' : '🌐' ?>
                        </a>

                        <a href="<?= Url::to(['/garage/delete/' . $build->id]) ?>"
                           class="as-btn as-btn--ghost as-btn--sm"
                           style="color:var(--accent-red)"
                           onclick="return confirm('Удалить сборку?')">🗑</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="as-empty" style="margin-bottom:48px">
            <div class="as-empty__icon">🚗</div>
            <div class="as-empty__title">Гараж пуст</div>
            <p class="as-empty__text">Создай свою первую 3D-сборку</p>
            <a href="<?= Url::to(['/constructor']) ?>" class="as-btn as-btn--primary mt-16">Начать сборку</a>
        </div>
    <?php endif; ?>

    <!-- Последняя активность -->
    <?php if ($recentLikes): ?>
        <div>
            <h2 style="font-family:var(--font-head);font-size:1.3rem;margin-bottom:20px">Последняя активность</h2>
            <div class="as-card" style="padding:4px 20px">
                <?php foreach ($recentLikes as $like): ?>
                    <div class="as-activity-item">
                        <div class="as-activity-item__icon">❤</div>
                        <div>
                            <div class="as-activity-item__text">
                                Кто-то оценил <a href="<?= Url::to(['/gallery/' . $like->build_id]) ?>"
                                                 style="color:var(--accent)"><?= Html::encode($like->build->name ?? '—') ?></a>
                            </div>
                            <div class="as-activity-item__time"><?= date('d.m.Y H:i', $like->created_at) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>