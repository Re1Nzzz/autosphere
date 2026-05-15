<?php
/** @var yii\web\View $this */
/** @var app\models\User[] $users */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;
?>
    <div class="as-admin-layout">
        <?= $this->render('_sidebar') ?>
        <div class="as-admin-content">
            <h1 style="font-family:var(--font-head);font-size:1.8rem;margin-bottom:32px">
                Пользователи (<?= count($users) ?>)
            </h1>

            <div class="as-table__wrap">
                <table class="as-table">
                    <thead>
                    <tr>
                        <th>ID</th><th>Пользователь</th><th>Email</th>
                        <th>Роль</th><th>Статус</th><th>Дата</th><th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="color:var(--text-3)"><?= $u->id ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                <span class="as-author-avatar">
                                    <?= $u->getAvatarLetter() ?>
                                </span>
                                    <?= Html::encode($u->username) ?>
                                </div>
                            </td>
                            <td style="color:var(--text-3)"><?= Html::encode($u->email) ?></td>
                            <td>
                            <span class="as-badge as-badge--<?= $u->role === 'admin' ? 'red' : ($u->role === 'moderator' ? 'orange' : 'blue') ?>">
                                <?= $u->role ?>
                            </span>
                            </td>
                            <td>
                                <?php if ($u->status === User::STATUS_ACTIVE): ?>
                                    <span class="as-badge as-badge--green">Активен</span>
                                <?php else: ?>
                                    <span class="as-badge as-badge--red">Заблокирован</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-3);font-size:.8rem"><?= date('d.m.Y', $u->created_at) ?></td>
                            <td>
                                <?php if (!$u->isAdmin()): ?>
                                    <button class="as-btn as-btn--ghost as-btn--sm"
                                            onclick="openEditModal(<?= $u->id ?>, '<?= $u->username ?>', '<?= $u->role ?>', <?= $u->status ?>)">
                                        ✏ Изменить
                                    </button>
                                <?php else: ?>
                                    <span style="color:var(--text-3);font-size:.8rem">Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования пользователя -->
    <div class="as-modal-overlay" id="editUserModal">
        <div class="as-modal">
            <div class="as-modal__head">
                <span class="as-modal__title" id="editUserTitle">Редактировать пользователя</span>
                <button class="as-modal__close" onclick="closeModal('editUserModal')">×</button>
            </div>
            <form class="as-form" method="post" action="<?= Url::to(['/admin/users']) ?>">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="user_id" id="editUserId">
                <div class="as-form-group">
                    <label class="as-form-label">Роль</label>
                    <select name="role" id="editUserRole" class="as-form-input">
                        <option value="user">user</option>
                        <option value="moderator">moderator</option>
                    </select>
                </div>
                <div class="as-form-group">
                    <label class="as-form-label">Статус</label>
                    <select name="status" id="editUserStatus" class="as-form-input">
                        <option value="1">Активен</option>
                        <option value="0">Заблокирован</option>
                    </select>
                </div>
                <button type="submit" class="as-btn as-btn--primary as-btn--full">Сохранить</button>
            </form>
        </div>
    </div>

<?php $this->registerJs(<<<JS
function openEditModal(id, username, role, status) {
    document.getElementById('editUserTitle').textContent = 'Редактировать: ' + username;
    document.getElementById('editUserId').value     = id;
    document.getElementById('editUserRole').value   = role;
    document.getElementById('editUserStatus').value = status;
    openModal('editUserModal');
}
JS) ?>