<?php
/** @var yii\web\View $this */
/** @var app\models\Build[] $latestBuilds */
/** @var app\models\Build[] $topBuilds */
/** @var array $stats */

use yii\helpers\Html;
use yii\helpers\Url;
?>
    <!-- ===================== HERO ===================== -->
    <section class="as-hero">
        <div class="as-hero__bg">
            <div class="as-hero__bg-glow"></div>
            <div class="as-hero__bg-glow2"></div>
            <div class="as-hero__grid"></div>
        </div>
        <div class="as-hero__inner">
            <div class="as-hero__content">
                <div class="as-hero__badge">
                    <span class="as-hero__badge-dot"></span>
                    3D Платформа тюнинга
                </div>
                <h1 class="as-hero__title">
                    Собери.<br>
                    <span class="gradient-text">Прокачай.</span><br>
                    Покажи миру.
                </h1>
                <p class="as-hero__sub">
                    Создавай уникальные 3D-сборки, делись работами с сообществом,
                    находи события на интерактивной карте и общайся в чате.
                </p>
                <div class="as-hero__btns">
                    <a href="<?= Url::to(['/constructor']) ?>" class="as-btn as-btn--primary as-btn--lg">
                        ⚡ Начать сборку
                    </a>
                    <a href="<?= Url::to(['/gallery']) ?>" class="as-btn as-btn--ghost as-btn--lg">
                        Галерея работ
                    </a>
                </div>
                <div class="as-hero__stats">
                    <div>
                        <span class="as-hero__stat-val"><?= number_format($stats['builds']) ?></span>
                        <span class="as-hero__stat-label">Сборок</span>
                    </div>
                    <div>
                        <span class="as-hero__stat-val"><?= number_format($stats['users']) ?></span>
                        <span class="as-hero__stat-label">Гонщиков</span>
                    </div>
                    <div>
                        <span class="as-hero__stat-val"><?= number_format($stats['points']) ?></span>
                        <span class="as-hero__stat-label">Точек на карте</span>
                    </div>
                </div>
            </div>
            <div class="as-hero__visual">
                <div class="as-hero__canvas-wrap">
                    <canvas id="heroCanvas"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== ЛЕНТА РАБОТ ===================== -->
<?php if ($latestBuilds): ?>
    <section class="as-ribbon">
        <div class="as-ribbon__track" id="ribbonTrack">
            <?php foreach ($latestBuilds as $b): ?>
                <a href="<?= Url::to(['/gallery/' . $b->id]) ?>" class="as-ribbon__item">
                    <?php if ($b->screenshot): ?>
                        <img src="<?= Html::encode($b->screenshot) ?>" alt="<?= Html::encode($b->name) ?>">
                    <?php else: ?>
                        <div class="as-ribbon__item-placeholder">🚗</div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

    <!-- ===================== ТОП НЕДЕЛИ ===================== -->
<?php if ($topBuilds): ?>
    <section class="as-section">
        <div class="as-section__head">
            <span class="as-section__label">🔥 Топ недели</span>
            <h2 class="as-section__title">Лучшие сборки</h2>
            <p class="as-section__sub">Самые популярные проекты сообщества за последние 7 дней</p>
        </div>
        <div class="as-grid as-grid--3">
            <?php foreach ($topBuilds as $build): ?>
                <a href="<?= Url::to(['/gallery/' . $build->id]) ?>" class="as-card as-build-card">
                    <div class="as-build-card__screenshot">
                        <?php if ($build->screenshot): ?>
                            <img src="<?= Html::encode($build->screenshot) ?>" alt="<?= Html::encode($build->name) ?>">
                        <?php else: ?>
                            <span>🚗</span>
                        <?php endif; ?>
                        <div class="as-build-card__actions">
                            <span class="as-like-btn">❤ <?= $build->likes_count ?></span>
                        </div>
                    </div>
                    <div class="as-card__body">
                        <div class="as-card__title"><?= Html::encode($build->name) ?></div>
                        <div class="as-card__meta">
                    <span class="as-author-chip">
                        <span class="as-author-avatar"><?= $build->user ? mb_strtoupper(mb_substr($build->user->username,0,1)) : '?' ?></span>
                        <?= Html::encode($build->user->username ?? 'Unknown') ?>
                    </span>
                            <span>👁 <?= $build->views_count ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-32">
            <a href="<?= Url::to(['/gallery']) ?>" class="as-btn as-btn--ghost">Смотреть все работы →</a>
        </div>
    </section>
<?php endif; ?>

    <!-- ===================== ПРЕИМУЩЕСТВА ===================== -->
    <section class="as-section" style="padding-top:0">
        <div class="as-section__head">
            <span class="as-section__label">Возможности</span>
            <h2 class="as-section__title">Всё для настоящего гонщика</h2>
        </div>
        <div class="as-advantages">
            <div class="as-adv-card">
                <div class="as-adv-card__icon">🎮</div>
                <div class="as-adv-card__title">3D Конструктор</div>
                <p class="as-adv-card__text">Интерактивная сборка с реальными 3D-моделями на базе Three.js. Кузов, диски, подвеска, цвет — всё настраивается в реальном времени.</p>
            </div>
            <div class="as-adv-card">
                <div class="as-adv-card__icon">🗺</div>
                <div class="as-adv-card__title">Интерактивная карта</div>
                <p class="as-adv-card__text">Находи события, СТО и магазины рядом. Добавляй свои точки интереса и делись ими с сообществом.</p>
            </div>
            <div class="as-adv-card">
                <div class="as-adv-card__icon">💬</div>
                <div class="as-adv-card__title">Живой чат</div>
                <p class="as-adv-card__text">Общайся с тысячами автолюбителей в реальном времени. Модерируемая среда без спама и токсичности.</p>
            </div>
        </div>
    </section>

    <!-- ===================== ПРЕВЬЮ КАРТЫ ===================== -->
    <section class="as-section" style="padding-top:0">
        <div class="as-map-preview" onclick="window.location='<?= Url::to(['/map']) ?>'">
            <div class="as-map-preview__img" style="background: var(--bg-3); width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:4rem; opacity:0.3">🗺</span>
            </div>
            <div class="as-map-preview__overlay">
                <div class="as-map-preview__title">📍 Карта событий</div>
                <a href="<?= Url::to(['/map']) ?>" class="as-btn as-btn--primary">Перейти к событиям</a>
            </div>
        </div>
    </section>

    <!-- ===================== HERO 3D (Three.js) ===================== -->
<?php
$this->registerJs(<<<JS
(function() {
    const canvas = document.getElementById('heroCanvas');
    if (!canvas || typeof THREE === 'undefined') return;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(canvas.offsetWidth, canvas.offsetHeight);
    renderer.setClearColor(0x000000, 0);

    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, canvas.offsetWidth / canvas.offsetHeight, 0.1, 100);
    camera.position.set(0, 1, 5);

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    const dLight = new THREE.DirectionalLight(0x6366f1, 2);
    dLight.position.set(5, 5, 5);
    scene.add(dLight);
    const dLight2 = new THREE.DirectionalLight(0x8b5cf6, 1);
    dLight2.position.set(-5, 2, -3);
    scene.add(dLight2);

    // Grid
    const grid = new THREE.GridHelper(20, 20, 0x6366f1, 0x1a1a2e);
    grid.position.y = -1.5;
    scene.add(grid);

    // Car body (simplified box-car)
    const bodyGeo  = new THREE.BoxGeometry(3.2, 0.5, 1.4);
    const bodyMat  = new THREE.MeshStandardMaterial({ color: 0x6366f1, metalness: 0.8, roughness: 0.2 });
    const body     = new THREE.Mesh(bodyGeo, bodyMat);
    scene.add(body);

    const cabinGeo = new THREE.BoxGeometry(1.6, 0.5, 1.2);
    const cabin    = new THREE.Mesh(cabinGeo, new THREE.MeshStandardMaterial({ color: 0x1a1a2e, metalness: 0.5, roughness: 0.3 }));
    cabin.position.set(-0.1, 0.5, 0);
    scene.add(cabin);

    // Wheels
    const wGeo = new THREE.CylinderGeometry(0.35, 0.35, 0.2, 24);
    const wMat = new THREE.MeshStandardMaterial({ color: 0x0a0a0a, roughness: 0.8 });
    const wheelPositions = [[-1.1,-0.5,0.75],[1.1,-0.5,0.75],[-1.1,-0.5,-0.75],[1.1,-0.5,-0.75]];
    wheelPositions.forEach(([x,y,z]) => {
        const w = new THREE.Mesh(wGeo, wMat);
        w.rotation.z = Math.PI / 2;
        w.position.set(x, y, z);
        scene.add(w);
        // Rim
        const rim = new THREE.Mesh(
            new THREE.CylinderGeometry(0.28, 0.28, 0.22, 12),
            new THREE.MeshStandardMaterial({ color: 0xc9a14a, metalness: 1, roughness: 0.1 })
        );
        rim.rotation.z = Math.PI / 2;
        rim.position.set(x, y, z);
        scene.add(rim);
    });

    // Particles
    const pGeo = new THREE.BufferGeometry();
    const pVerts = [];
    for (let i = 0; i < 200; i++) {
        pVerts.push((Math.random()-0.5)*20, (Math.random()-0.5)*10, (Math.random()-0.5)*20);
    }
    pGeo.setAttribute('position', new THREE.Float32BufferAttribute(pVerts, 3));
    const particles = new THREE.Points(pGeo, new THREE.PointsMaterial({ color: 0x6366f1, size: 0.05 }));
    scene.add(particles);

    let t = 0;
    function animate() {
        requestAnimationFrame(animate);
        t += 0.01;
        body.rotation.y  = Math.sin(t * 0.5) * 0.3;
        cabin.rotation.y = body.rotation.y;
        particles.rotation.y += 0.001;
        grid.position.z  = ((t * 2) % 1) - 0.5;
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const w = canvas.offsetWidth, h = canvas.offsetHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });
})();
JS, \yii\web\View::POS_END);
?>