<?php
/** @var yii\web\View $this */
/** @var app\models\Build $build */
/** @var array $stats   [speed, handling, weight] */
/** @var bool  $isLiked */
/** @var app\models\Comment $comment */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Json;

$config     = $build->getConfig();
$colorHex   = '#6366f1';
if (!empty($config['color'])) {
    $cp = \app\models\CarPart::findOne((int)$config['color']);
    if ($cp && $cp->color_hex) $colorHex = $cp->color_hex;
}
?>

    <div class="as-build-detail">
        <!-- Левая колонка: вьювер + инфо -->
        <div>
            <!-- 3D Viewer -->
            <div class="as-viewer mb-24">
                <canvas id="viewerCanvas"></canvas>
            </div>

            <!-- Характеристики -->
            <div class="as-card" style="padding:24px;margin-bottom:24px">
                <h3 style="font-family:var(--font-head);font-size:1.1rem;margin-bottom:16px">Характеристики</h3>
                <?php foreach (['speed' => 'Скорость', 'handling' => 'Управление', 'weight' => 'Лёгкость'] as $key => $label): ?>
                    <div class="as-stat-row" style="margin-bottom:12px">
                        <span class="as-stat-label"><?= $label ?></span>
                        <div class="as-stat-bar" style="flex:1;height:8px;background:var(--bg-3);border-radius:4px;overflow:hidden;margin:0 12px">
                            <div style="height:100%;width:<?= $stats[$key] ?>%;background:var(--grad-primary);border-radius:4px"></div>
                        </div>
                        <span class="as-stat-val"><?= $stats[$key] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Состав сборки -->
            <?php if ($config): ?>
                <div class="as-card" style="padding:24px">
                    <h3 style="font-family:var(--font-head);font-size:1.1rem;margin-bottom:16px">Состав</h3>
                    <?php foreach ($config as $cat => $partId): ?>
                        <?php $part = \app\models\CarPart::findOne((int)$partId); ?>
                        <?php if ($part): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
                                <span style="color:var(--text-3);font-size:.8rem"><?= $part->getCategoryLabel() ?></span>
                                <span style="font-size:.875rem"><?= Html::encode($part->name) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Правая колонка: инфо + комментарии -->
        <div>
            <!-- Заголовок -->
            <div class="as-card" style="padding:28px;margin-bottom:20px">
                <h1 style="font-family:var(--font-head);font-size:1.8rem;font-weight:700;margin-bottom:12px">
                    <?= Html::encode($build->name) ?>
                </h1>

                <div class="as-author-chip" style="margin-bottom:20px">
                <span class="as-author-avatar" style="width:36px;height:36px">
                    <?= mb_strtoupper(mb_substr($build->user->username ?? '?', 0, 1)) ?>
                </span>
                    <span style="font-size:.9rem"><?= Html::encode($build->user->username ?? 'Unknown') ?></span>
                    <span style="color:var(--text-3);font-size:.8rem">· <?= date('d.m.Y', $build->created_at) ?></span>
                </div>

                <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
                <span style="display:flex;align-items:center;gap:6px;color:var(--text-2);font-size:.875rem">
                    👁 <?= number_format($build->views_count) ?> просмотров
                </span>
                    <span style="display:flex;align-items:center;gap:6px;color:var(--text-2);font-size:.875rem">
                    💬 <?= count($build->comments) ?> комментариев
                </span>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <!-- Лайк -->
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <button class="as-btn <?= $isLiked ? 'as-btn--danger' : 'as-btn--ghost' ?> as-like-btn"
                                data-build="<?= $build->id ?>">
                            ❤ <span class="as-like-count"><?= $build->likes_count ?></span>
                        </button>
                    <?php else: ?>
                        <span class="as-btn as-btn--ghost">❤ <?= $build->likes_count ?></span>
                    <?php endif; ?>

                    <!-- Редактировать (свои) -->
                    <?php if (!Yii::$app->user->isGuest && $build->user_id === Yii::$app->user->id): ?>
                        <a href="<?= Url::to(['/constructor', 'build' => $build->id]) ?>"
                           class="as-btn as-btn--ghost as-btn--sm">✏ Редактировать</a>
                    <?php endif; ?>

                    <a href="<?= Url::to(['/gallery']) ?>" class="as-btn as-btn--ghost as-btn--sm">← Назад</a>
                </div>
            </div>

            <!-- Комментарии -->
            <div class="as-card" style="padding:24px">
                <h3 style="font-family:var(--font-head);font-size:1.1rem;margin-bottom:20px">
                    Комментарии (<?= count($build->comments) ?>)
                </h3>

                <!-- Форма комментария -->
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?php $form = ActiveForm::begin([
                        'action' => Url::to(['/gallery/comment/' . $build->id]),
                        'options' => ['class' => 'as-form', 'style' => 'margin-bottom:24px'],
                        'fieldConfig' => [
                            'template'     => '{input}{error}',
                            'inputOptions' => ['class' => 'as-form-textarea'],
                            'errorOptions' => ['class' => 'help-block-error'],
                        ],
                    ]) ?>
                    <?= $form->field($comment, 'text')->textarea(['rows' => 3, 'placeholder' => 'Твой комментарий...']) ?>
                    <button type="submit" class="as-btn as-btn--primary as-btn--sm">Отправить</button>
                    <?php ActiveForm::end() ?>
                <?php else: ?>
                    <p style="color:var(--text-3);font-size:.875rem;margin-bottom:20px">
                        <a href="<?= Url::to(['/auth/login']) ?>" style="color:var(--accent)">Войди</a>, чтобы оставить комментарий
                    </p>
                <?php endif; ?>

                <!-- Список комментариев -->
                <?php if ($build->comments): ?>
                    <div style="display:flex;flex-direction:column;gap:16px">
                        <?php foreach ($build->comments as $c): ?>
                            <div style="display:flex;gap:10px">
                            <span class="as-author-avatar" style="width:32px;height:32px;font-size:.75rem;flex-shrink:0">
                                <?= mb_strtoupper(mb_substr($c->user->username ?? '?', 0, 1)) ?>
                            </span>
                                <div>
                                    <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px">
                                        <span style="font-weight:600;font-size:.875rem"><?= Html::encode($c->user->username ?? 'Unknown') ?></span>
                                        <span style="color:var(--text-3);font-size:.75rem"><?= date('d.m.Y H:i', $c->created_at) ?></span>
                                    </div>
                                    <p style="font-size:.875rem;color:var(--text-2);line-height:1.5"><?= Html::encode($c->text) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-3);font-size:.875rem">Пока нет комментариев. Будь первым!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
/* 3D Viewer (mini) */
$colorHexJs = Json::encode($colorHex);
$this->registerJs(<<<JS
(function() {
    const canvas = document.getElementById('viewerCanvas');
    if (!canvas || typeof THREE === 'undefined') return;
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
    renderer.setSize(canvas.offsetWidth, canvas.offsetHeight || 360);
    renderer.setClearColor(0x080810);
    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, canvas.offsetWidth / (canvas.offsetHeight || 360), 0.1, 100);
    camera.position.set(0, 1.5, 6);
    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const d = new THREE.DirectionalLight(0xffffff, 1.5); d.position.set(5,8,5); scene.add(d);
    const grid = new THREE.GridHelper(20, 20, 0x6366f1, 0x1a1a2e); grid.position.y = -1.2; scene.add(grid);
    const body = new THREE.Mesh(new THREE.BoxGeometry(3.2,.55,1.5), new THREE.MeshStandardMaterial({color: $colorHexJs, metalness:.85, roughness:.15}));
    scene.add(body);
    const cabin = new THREE.Mesh(new THREE.BoxGeometry(1.6,.52,1.3), new THREE.MeshStandardMaterial({color:0x1a1a2e}));
    cabin.position.set(-.1,.52,0); scene.add(cabin);
    [[-1.15,-.55,.8],[1.15,-.55,.8],[-1.15,-.55,-.8],[1.15,-.55,-.8]].forEach(([x,y,z]) => {
        const w = new THREE.Mesh(new THREE.CylinderGeometry(.36,.36,.22,24), new THREE.MeshStandardMaterial({color:0x111111,roughness:.9}));
        w.rotation.z = Math.PI/2; w.position.set(x,y,z); scene.add(w);
    });
    let t = 0;
    (function loop() { requestAnimationFrame(loop); t += 0.008; camera.position.x = Math.sin(t)*6; camera.position.z = Math.cos(t)*6; camera.lookAt(0,0,0); renderer.render(scene,camera); })();
    window.addEventListener('resize', () => { const w=canvas.offsetWidth,h=canvas.offsetHeight||360; renderer.setSize(w,h); camera.aspect=w/h; camera.updateProjectionMatrix(); });
})();
JS, \yii\web\View::POS_END);
?>