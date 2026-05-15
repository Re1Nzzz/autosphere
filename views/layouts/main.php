<?php
/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

// Leaflet CSS
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="<?= Yii::$app->params['siteDesc'] ?>">
        <title><?= Html::encode($this->title ?? Yii::$app->params['siteName']) ?> — AutoSphere 3D</title>
        <?php $this->head() ?>
    </head>
    <body class="as-body">
    <?php $this->beginBody() ?>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="as-nav" id="asNav">
        <div class="as-nav__inner">
            <a href="<?= Url::to(['/']) ?>" class="as-nav__logo">
                <span class="as-logo-icon">⬡</span>
                <span class="as-logo-text">Auto<strong>Sphere</strong></span>
            </a>

            <ul class="as-nav__links" id="navLinks">
                <li><a href="<?= Url::to(['/']) ?>"
                       class="<?= Yii::$app->controller->id==='site' && Yii::$app->controller->action->id==='index' ? 'active':'' ?>">
                        Главная</a></li>
                <li><a href="<?= Url::to(['/constructor']) ?>"
                       class="<?= Yii::$app->controller->id==='constructor'?'active':'' ?>">
                        3D Конструктор</a></li>
                <li><a href="<?= Url::to(['/gallery']) ?>"
                       class="<?= Yii::$app->controller->id==='gallery'?'active':'' ?>">
                        Галерея</a></li>
                <li><a href="<?= Url::to(['/map']) ?>"
                       class="<?= Yii::$app->controller->id==='map'?'active':'' ?>">
                        Карта</a></li>
                <li><a href="<?= Url::to(['/chat']) ?>"
                       class="<?= Yii::$app->controller->id==='chat'?'active':'' ?>">
                        Чат</a></li>
            </ul>

            <div class="as-nav__auth">
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Url::to(['/auth/login']) ?>" class="as-btn as-btn--ghost as-btn--sm">Войти</a>
                    <a href="<?= Url::to(['/auth/register']) ?>" class="as-btn as-btn--primary as-btn--sm">Регистрация</a>
                <?php else: ?>
                    <?php if (Yii::$app->user->identity->role === 'admin'): ?>
                        <a href="<?= Url::to(['/admin']) ?>" class="as-btn as-btn--ghost as-btn--sm">Админ</a>
                    <?php endif; ?>
                    <a href="<?= Url::to(['/garage']) ?>" class="as-nav__avatar-link">
                        <?php $avatar = Yii::$app->user->identity->avatar; ?>
                        <?php if ($avatar): ?>
                            <img src="<?= Html::encode($avatar) ?>" alt="Avatar" class="as-nav__avatar">
                        <?php else: ?>
                            <div class="as-nav__avatar as-nav__avatar--placeholder">
                                <?= mb_substr(Yii::$app->user->identity->username, 0, 1) ?>
                            </div>
                        <?php endif; ?>
                        <span><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                    </a>
                    <a href="<?= Url::to(['/auth/logout']) ?>"
                       data-method="post"
                       class="as-btn as-btn--ghost as-btn--sm">Выйти</a>
                <?php endif; ?>
            </div>

            <button class="as-nav__burger" id="navBurger" aria-label="Меню">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- ===================== FLASH MESSAGES ===================== -->
    <div class="as-flash-wrap">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
            <?php foreach ((array)$messages as $msg): ?>
                <div class="as-flash as-flash--<?= $type ?>">
                    <?= Html::encode($msg) ?>
                    <button class="as-flash__close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- ===================== CONTENT ===================== -->
    <main class="as-main">
        <?= $content ?>
    </main>

    <!-- ===================== FOOTER ===================== -->
    <footer class="as-footer">
        <div class="as-footer__inner">
            <div class="as-footer__brand">
                <span class="as-logo-icon">⬡</span>
                <span class="as-logo-text">Auto<strong>Sphere</strong></span>
                <p>Платформа автомобильного 3D-тюнинга</p>
            </div>
            <div class="as-footer__links">
                <div class="as-footer__col">
                    <h4>Навигация</h4>
                    <a href="<?= Url::to(['/']) ?>">Главная</a>
                    <a href="<?= Url::to(['/constructor']) ?>">Конструктор</a>
                    <a href="<?= Url::to(['/gallery']) ?>">Галерея</a>
                </div>
                <div class="as-footer__col">
                    <h4>Сообщество</h4>
                    <a href="<?= Url::to(['/map']) ?>">Карта</a>
                    <a href="<?= Url::to(['/chat']) ?>">Чат</a>
                    <a href="<?= Url::to(['/contact']) ?>">Контакты</a>
                </div>
                <div class="as-footer__col">
                    <h4>Соцсети</h4>
                    <a href="#" target="_blank">Telegram</a>
                    <a href="#" target="_blank">VK</a>
                    <a href="#" target="_blank">YouTube</a>
                </div>
            </div>
        </div>
        <div class="as-footer__bottom">
            <p>© <?= date('Y') ?> AutoSphere 3D. Все права защищены.</p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>