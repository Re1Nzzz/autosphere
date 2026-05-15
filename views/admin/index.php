<?php
/** @var yii\web\View $this */
/** @var array $stats */
/** @var app\models\User[] $recentUsers */
/** @var app\models\Build[] $recentBuilds */
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="as-admin-layout">
    <?= $this->render('_sidebar') ?>
    <div class="as-admin-content">
        <h1 style="font-family:var(--font-head);font-size:1.8rem;margin-bottom:32px">
            Панель администратора
        </h1>

        <!-- Статистика -->
        <div class="as-grid as-grid--4" style="margin-bottom:40px">
            <?php
            $cards = [
                ['👤', 'Пользователи',   $stats['users'],    '/admin/users',    'blue'],
                ['🚗', 'Сборок всего',   $stats['builds'],   '/gallery',        'green'],
                ['⚙️', 'Запчастей',      $stats['parts'],    '/admin/parts',    'orange'],
                ['✉️', 'Новых сообщений',$stats['messages'], '/admin/moderate', 'red'],
            ];
            foreach ($cards as [$icon, $label, $val, $url, $color]):
                ?>
                <a href="<?= Url::to([$url]) ?>" class="as-card" style="padding:24px;text-decoration:none">
                    <div style="font-size:2rem;margin-bottom:8px"><?= $icon ?></div>
                    <div style="font-family:var(--font-head);font-size:2rem;font-weight:700;color:var(--accent)">
                        <?= number_format($val) ?>
                    </div>
                    <div style="color:var(--text-3);font-size:.85rem"><?= $label ?></div>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <!-- Последние пользователи -->
            <div class="as-card" style="padding:24px">
                <div class="flex-between mb-16">
                    <h2 style="font-family:var(--font-head);font-size:1.1rem">Новые пользователи</h2>
                    <a href="<?= Url::to(['/admin/users']) ?>" style="color:var(--accent);font-size:.8rem">Все →</a>
                </div>
                <div class="as-table__wrap">
                    <table class="as-table">
                        <thead><tr><th>Пользователь</th><th>Роль</th><th>Дата</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td><?= Html::encode($u->username) ?></td>
                                <td>
                                    <span class="as-badge as-badge--<?= $u->role === 'admin' ? 'red' : ($u->role === 'moderator' ? 'orange' : 'blue') ?>">
                                        <?= $u->role ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-3);font-size:.8rem"><?= date('d.m.Y', $u->created_at) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Последние сборки -->
            <div class="as-card" style="padding:24px">
                <div class="flex-between mb-16">
                    <h2 style="font-family:var(--font-head);font-size:1.1rem">Последние сборки</h2>
                    <a href="<?= Url::to(['/gallery']) ?>" style="color:var(--accent);font-size:.8rem">Галерея →</a>
                </div>
                <div class="as-table__wrap">
                    <table class="as-table">
                        <thead><tr><th>Сборка</th><th>Автор</th><th>❤</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentBuilds as $b): ?>
                            <tr>
                                <td>
                                    <a href="<?= Url::to(['/gallery/' . $b->id]) ?>" style="color:var(--accent)">
                                        <?= Html::encode(mb_strimwidth($b->name, 0, 24, '…')) ?>
                                    </a>
                                </td>
                                <td style="color:var(--text-2)"><?= Html::encode($b->user->username ?? '—') ?></td>
                                <td style="color:var(--accent-red)"><?= $b->likes_count ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>