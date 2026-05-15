<?php
/** @var yii\web\View $this */
/** @var app\models\Build[] $items */
/** @var int $total */
/** @var int $page */
/** @var int $pageSize */
/** @var array $filters */
/** @var array $brands */
/** @var int[] $likedIds */
use yii\helpers\Html;
use yii\helpers\Url;

$totalPages = ceil($total / $pageSize);
?>
<div class="as-section">
    <div class="as-section__head">
        <span class="as-section__label">Сообщество</span>
        <h1 class="as-section__title">Галерея сборок</h1>
        <p class="as-section__sub">Лучшие проекты участников AutoSphere — <?= number_format($total) ?> работ</p>
    </div>

    <!-- Фильтры -->
    <form id="galleryFilterForm" method="get" action="<?= Url::to(['/gallery']) ?>">
        <div class="as-gallery-filters">
            <input class="as-filter-input" type="text" name="q" placeholder="🔍 Поиск по названию..."
                   value="<?= Html::encode($filters['q']) ?>">

            <select class="as-filter-select" name="brand">
                <option value="">Все марки</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= Html::encode($b['name']) ?>"
                        <?= $filters['brand'] === $b['name'] ? 'selected' : '' ?>>
                        <?= Html::encode($b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="as-filter-select" name="sort">
                <option value="latest"  <?= $filters['sort'] === 'latest'  ? 'selected' : '' ?>>Новые</option>
                <option value="popular" <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Популярные</option>
            </select>

            <button type="submit" class="as-btn as-btn--primary as-btn--sm">Найти</button>
            <?php if ($filters['q'] || $filters['brand'] || $filters['sort'] !== 'latest'): ?>
                <a href="<?= Url::to(['/gallery']) ?>" class="as-btn as-btn--ghost as-btn--sm">Сбросить</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Сетка -->
    <?php if ($items): ?>
        <div class="as-grid as-grid--3">
            <?php foreach ($items as $build): ?>
                <div class="as-card as-build-card">
                    <a href="<?= Url::to(['/gallery/' . $build->id]) ?>">
                        <div class="as-build-card__screenshot">
                            <?php if ($build->screenshot): ?>
                                <img src="<?= Html::encode($build->screenshot) ?>" alt="<?= Html::encode($build->name) ?>">
                            <?php else: ?>
                                <span>🚗</span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="as-build-card__actions">
                        <?php if (!Yii::$app->user->isGuest): ?>
                            <button class="as-like-btn <?= in_array($build->id, $likedIds) ? 'liked' : '' ?>"
                                    data-build="<?= $build->id ?>">
                                ❤ <span class="as-like-count"><?= $build->likes_count ?></span>
                            </button>
                        <?php else: ?>
                            <span class="as-like-btn">❤ <?= $build->likes_count ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="as-card__body">
                        <a href="<?= Url::to(['/gallery/' . $build->id]) ?>">
                            <div class="as-card__title"><?= Html::encode($build->name) ?></div>
                        </a>
                        <div class="as-card__meta mt-8">
                            <span class="as-author-chip">
                                <span class="as-author-avatar">
                                    <?= mb_strtoupper(mb_substr($build->user->username ?? '?', 0, 1)) ?>
                                </span>
                                <?= Html::encode($build->user->username ?? 'Unknown') ?>
                            </span>
                            <span>👁 <?= number_format($build->views_count) ?></span>
                            <span>💬 <?= $build->getComments()->count() ?></span>
                        </div>
                    </div>

                    <div class="as-card__foot">
                        <span style="font-size:.75rem;color:var(--text-3)">
                            <?= date('d.m.Y', $build->created_at) ?>
                        </span>
                        <a href="<?= Url::to(['/gallery/' . $build->id]) ?>" class="as-btn as-btn--ghost as-btn--sm">
                            Подробнее →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <div class="as-pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php $params = array_merge($filters, ['page' => $i]); ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><span><?= $i ?></span></span>
                    <?php else: ?>
                        <a href="<?= Url::to(array_merge(['/gallery'], $params)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="as-empty">
            <div class="as-empty__icon">🔍</div>
            <div class="as-empty__title">Ничего не найдено</div>
            <p class="as-empty__text">Попробуй изменить фильтры или <a href="<?= Url::to(['/constructor']) ?>" style="color:var(--accent)">создай свою сборку</a></p>
        </div>
    <?php endif; ?>
</div>