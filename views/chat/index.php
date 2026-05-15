<?php
/** @var yii\web\View $this */
/** @var app\models\ChatMessage[] $messages */
/** @var app\models\User[] $onlineUsers */
use yii\helpers\Html;
use yii\helpers\Url;

$isGuest    = Yii::$app->user->isGuest;
$currentUid = $isGuest ? 0 : (int)Yii::$app->user->id;
$isMod      = !$isGuest && Yii::$app->user->identity->isModerator();
$lastId     = $messages ? end($messages)->id : 0;
?>

    <div class="as-chat-page">

        <!-- Основной чат -->
        <div class="as-chat-messages">
            <div class="as-chat-head">
                <div>
                    <h1 class="as-chat-head__title">
                        <span class="as-online-dot"></span>Чат сообщества
                    </h1>
                    <span style="color:var(--text-3);font-size:.8rem"><?= count($onlineUsers) ?> активных участников</span>
                </div>
                <?php if (!$isGuest && $isMod): ?>
                    <span class="as-badge as-badge--orange">👮 Модератор</span>
                <?php elseif (!$isGuest && Yii::$app->user->identity->isAdmin()): ?>
                    <span class="as-badge as-badge--red">⚡ Администратор</span>
                <?php endif; ?>
            </div>

            <!-- Лог сообщений -->
            <div class="as-chat-log" id="chatLog" data-last-id="<?= $lastId ?>">
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $u      = $msg->user;
                    $isOwn  = ($msg->user_id === $currentUid);
                    $letter = $u ? $u->getAvatarLetter() : '?';
                    $role   = $u ? $u->role : 'user';
                    ?>
                    <div class="as-msg<?= $isOwn ? ' as-msg--own' : '' ?>" data-msg-id="<?= $msg->id ?>">
                        <div class="as-msg__avatar"><?= $letter ?></div>
                        <div class="as-msg__body">
                            <div class="as-msg__head">
                            <span class="as-msg__name as-msg__name--<?= $role ?>">
                                <?= Html::encode($u->username ?? 'Unknown') ?>
                            </span>
                                <span class="as-msg__time"><?= date('H:i', $msg->created_at) ?></span>
                            </div>
                            <div class="as-msg__text<?= $msg->is_deleted ? ' as-msg__text--deleted' : '' ?>">
                                <?= $msg->is_deleted ? '[Удалено]' : Html::encode($msg->text) ?>
                            </div>
                            <?php if (!$isGuest && !$msg->is_deleted && ($isOwn || $isMod)): ?>
                                <div class="as-msg__actions">
                                    <button class="as-msg__action-btn"
                                            onclick="deleteMsg(<?= $msg->id ?>)">Удалить</button>
                                    <?php if ($isMod && !$isOwn && $u && !$u->isAdmin()): ?>
                                        <button class="as-msg__action-btn"
                                                onclick="banUser(<?= $msg->user_id ?>)">Бан</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!$messages): ?>
                    <div class="as-empty" style="padding:40px 0">
                        <div class="as-empty__icon">💬</div>
                        <div class="as-empty__title">Пока тишина</div>
                        <p class="as-empty__text">Будь первым, кто напишет!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Поле ввода -->
            <?php if (!$isGuest): ?>
                <form class="as-chat-input-area" id="chatForm">
                    <input class="as-chat-input" type="text" id="chatInput"
                           placeholder="Напиши сообщение... (Enter — отправить)"
                           autocomplete="off" maxlength="500">
                    <button type="submit" class="as-btn as-btn--primary">Отправить</button>
                </form>
            <?php else: ?>
                <div class="as-chat-input-area" style="justify-content:center">
                    <a href="<?= Url::to(['/auth/login']) ?>" class="as-btn as-btn--primary">
                        🔒 Войди, чтобы писать в чат
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Онлайн сайдбар -->
        <div class="as-chat-sidebar">
            <div class="as-chat-sidebar__title">Активные участники</div>
            <?php foreach ($onlineUsers as $u): ?>
                <div class="as-online-user">
                    <span class="as-online-dot"></span>
                    <span class="as-msg__name--<?= $u->role ?>" style="font-size:.8rem">
                    <?= Html::encode($u->username) ?>
                </span>
                </div>
            <?php endforeach; ?>
            <?php if (!$onlineUsers): ?>
                <p style="color:var(--text-3);font-size:.8rem">Нет активных</p>
            <?php endif; ?>

            <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
                <div class="as-chat-sidebar__title">Правила</div>
                <ul style="color:var(--text-3);font-size:.75rem;line-height:1.8;padding-left:12px">
                    <li>Уважай участников</li>
                    <li>Запрещён спам</li>
                    <li>Без нецензурных слов</li>
                    <li>По теме автомобилей</li>
                </ul>
            </div>
        </div>
    </div>

<?php
/* Enter = submit */
$this->registerJs(<<<JS
const chatInput = document.getElementById('chatInput');
chatInput?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm')?.dispatchEvent(new Event('submit'));
    }
});
JS, \yii\web\View::POS_END);
?>