<?php
/** @var yii\web\View $this */
/** @var array $parts   Grouped by category */
/** @var array $carModels */
/** @var app\models\Build|null $build */
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\CarPart;

$existingConfig = $build ? $build->getConfig() : [];
$buildId        = $build ? $build->id : 0;
$buildName      = $build ? $build->name : 'Моя сборка';
$isGuest        = Yii::$app->user->isGuest;
?>

    <div class="as-constructor" id="constructor">

        <!-- Категории (вертикальный сайдбар) -->
        <div class="as-constructor__sidebar">
            <?php foreach (CarPart::CATEGORY_ICONS as $cat => $icon): ?>
                <button class="as-cat-btn <?= $cat === 'body' ? 'active' : '' ?>"
                        data-cat="<?= $cat ?>"
                        title="<?= CarPart::CATEGORIES[$cat] ?>">
                    <span class="as-cat-btn__icon"><?= $icon ?></span>
                    <span class="as-cat-btn__label"><?= $cat === 'color' ? 'Цвет' : mb_substr(CarPart::CATEGORIES[$cat],0,4) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Canvas -->
        <div class="as-constructor__canvas-area">
            <canvas id="constructorCanvas"></canvas>

            <!-- Кнопки управления -->
            <div class="as-constructor__overlay-btns">
                <?php if (!$isGuest): ?>
                    <button class="as-btn as-btn--primary as-btn--sm" id="saveBuildBtn">💾 Сохранить</button>
                <?php else: ?>
                    <a href="<?= \yii\helpers\Url::to(['/auth/login']) ?>" class="as-btn as-btn--ghost as-btn--sm">🔒 Войди чтобы сохранить</a>
                <?php endif; ?>
                <button class="as-btn as-btn--ghost as-btn--sm" id="screenshotBtn">📸 Скриншот</button>
                <button class="as-btn as-btn--ghost as-btn--sm" id="resetCameraBtn">🎯 Сброс</button>
            </div>

            <!-- Характеристики -->
            <div class="as-constructor__stats-overlay" id="statsOverlay">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);margin-bottom:12px">Характеристики</div>
                <div class="as-stat-row">
                    <span class="as-stat-label">Скорость</span>
                    <div class="as-stat-bar"><div class="as-stat-bar__fill" id="statSpeed" style="width:50%"></div></div>
                    <span class="as-stat-val" id="statSpeedVal">50</span>
                </div>
                <div class="as-stat-row">
                    <span class="as-stat-label">Управление</span>
                    <div class="as-stat-bar"><div class="as-stat-bar__fill" id="statHandling" style="width:50%"></div></div>
                    <span class="as-stat-val" id="statHandlingVal">50</span>
                </div>
                <div class="as-stat-row">
                    <span class="as-stat-label">Вес</span>
                    <div class="as-stat-bar"><div class="as-stat-bar__fill" id="statWeight" style="width:50%;background:var(--accent-cyan)"></div></div>
                    <span class="as-stat-val" id="statWeightVal">50</span>
                </div>
            </div>

            <!-- Панель деталей -->
            <div class="as-constructor__parts-panel" id="partsPanel">
                <!-- заполняется JS -->
            </div>
        </div>
    </div>

    <!-- Модалка сохранения -->
    <div class="as-modal-overlay" id="saveModal">
        <div class="as-modal">
            <div class="as-modal__head">
                <span class="as-modal__title">Сохранить сборку</span>
                <button class="as-modal__close" onclick="closeModal('saveModal')">×</button>
            </div>
            <div class="as-form">
                <div class="as-form-group">
                    <label class="as-form-label">Название сборки</label>
                    <input type="text" class="as-form-input" id="buildNameInput" value="<?= Html::encode($buildName) ?>" placeholder="Мой BMW M3...">
                </div>
                <div class="as-form-group">
                    <label class="as-form-label">Автомобиль</label>
                    <select class="as-form-input" id="carModelSelect">
                        <option value="">— Выбери модель —</option>
                        <?php foreach ($carModels as $id => $name): ?>
                            <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="as-btn as-btn--primary as-btn--full" id="confirmSaveBtn">Сохранить</button>
            </div>
        </div>
    </div>

<?php
/* Передаём данные запчастей в JS */
$partsJson  = Json::encode($parts);
$configJson = Json::encode($existingConfig);

$this->registerJs(<<<JS
(function () {
    /* ===== DATA ===== */
    const PARTS_DATA  = $partsJson;
    const initConfig  = $configJson;
    const BUILD_ID    = $buildId;
    const CSRF        = document.querySelector('meta[name=csrf-token]')?.content || '';

    /* ===== STATE ===== */
    let currentCat    = 'body';
    let config        = Object.assign({body:1, wheels:6, suspension:11, color:17}, initConfig);

    /* ===== THREE.JS SETUP ===== */
    const canvas   = document.getElementById('constructorCanvas');
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.shadowMap.enabled = true;

    const scene  = new THREE.Scene();
    scene.background = new THREE.Color(0x080810);
    const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
    camera.position.set(0, 1.5, 6);

    function resizeRenderer() {
        const w = canvas.offsetWidth, h = canvas.offsetHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resizeRenderer();
    window.addEventListener('resize', resizeRenderer);

    /* Lights */
    scene.add(new THREE.AmbientLight(0xffffff, 0.6));
    const sun = new THREE.DirectionalLight(0xffffff, 1.5);
    sun.position.set(5, 8, 5);
    sun.castShadow = true;
    scene.add(sun);
    const fill = new THREE.DirectionalLight(0x8b5cf6, 0.8);
    fill.position.set(-5, 3, -3);
    scene.add(fill);

    /* Floor */
    const floor = new THREE.Mesh(
        new THREE.PlaneGeometry(20, 20),
        new THREE.MeshStandardMaterial({ color: 0x0d0d1a, roughness: 0.8 })
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -1.2;
    floor.receiveShadow = true;
    scene.add(floor);

    const grid = new THREE.GridHelper(20, 20, 0x6366f1, 0x1a1a2e);
    grid.position.y = -1.19;
    scene.add(grid);

    /* Car group */
    const carGroup = new THREE.Group();
    scene.add(carGroup);

    /* Body mesh */
    const bodyMesh = new THREE.Mesh(
        new THREE.BoxGeometry(3.2, 0.55, 1.5),
        new THREE.MeshStandardMaterial({ color: 0x6366f1, metalness: 0.85, roughness: 0.15 })
    );
    bodyMesh.castShadow = true;
    carGroup.add(bodyMesh);

    const cabin = new THREE.Mesh(
        new THREE.BoxGeometry(1.6, 0.52, 1.3),
        new THREE.MeshStandardMaterial({ color: 0x1a1a2e, metalness: 0.5, roughness: 0.25 })
    );
    cabin.position.set(-0.1, 0.52, 0);
    cabin.castShadow = true;
    carGroup.add(cabin);

    /* Wheels */
    const wheelMeshes = [];
    const wheelPositions = [[-1.15, -0.55, 0.8],[1.15, -0.55, 0.8],[-1.15, -0.55, -0.8],[1.15, -0.55, -0.8]];
    const rimMat = new THREE.MeshStandardMaterial({ color: 0xc9a14a, metalness: 1, roughness: 0.1 });
    wheelPositions.forEach(([x, y, z]) => {
        const wGrp = new THREE.Group();
        wGrp.position.set(x, y, z);
        const tire = new THREE.Mesh(
            new THREE.CylinderGeometry(0.36, 0.36, 0.22, 28),
            new THREE.MeshStandardMaterial({ color: 0x111111, roughness: 0.9 })
        );
        tire.rotation.z = Math.PI / 2;
        wGrp.add(tire);
        const rim = new THREE.Mesh(
            new THREE.CylinderGeometry(0.27, 0.27, 0.24, 12),
            rimMat
        );
        rim.rotation.z = Math.PI / 2;
        wGrp.add(rim);
        carGroup.add(wGrp);
        wheelMeshes.push({ group: wGrp, rim });
    });

    /* ===== ORBIT CONTROLS (manual) ===== */
    let isDragging = false, prevX = 0, prevY = 0;
    let rotY = 0, rotX = 0.2, zoom = 6;

    canvas.addEventListener('mousedown', e => { isDragging = true; prevX = e.clientX; prevY = e.clientY; });
    window.addEventListener('mouseup', () => isDragging = false);
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        rotY += (e.clientX - prevX) * 0.008;
        rotX  = Math.max(-0.4, Math.min(0.8, rotX + (e.clientY - prevY) * 0.005));
        prevX = e.clientX; prevY = e.clientY;
    });
    canvas.addEventListener('wheel', e => {
        zoom = Math.max(3, Math.min(10, zoom + e.deltaY * 0.01));
    });
    canvas.addEventListener('touchstart', e => { prevX = e.touches[0].clientX; });
    canvas.addEventListener('touchmove', e => {
        rotY += (e.touches[0].clientX - prevX) * 0.01;
        prevX = e.touches[0].clientX;
    });

    document.getElementById('resetCameraBtn')?.addEventListener('click', () => { rotY = 0; rotX = 0.2; zoom = 6; });

    /* ===== ANIMATION ===== */
    let t = 0;
    function animate() {
        requestAnimationFrame(animate);
        t += 0.016;
        camera.position.x = Math.sin(rotY) * zoom;
        camera.position.z = Math.cos(rotY) * zoom;
        camera.position.y = rotX * zoom;
        camera.lookAt(0, 0, 0);
        wheelMeshes.forEach(({ group }) => { group.children[0].rotation.x += 0.04; });
        renderer.render(scene, camera);
    }
    animate();

    /* ===== APPLY CONFIG ===== */
    const colorNames = {};
    if (PARTS_DATA.color) PARTS_DATA.color.forEach(p => { colorNames[p.id] = p.color_hex || '#6366f1'; });

    function applyConfig() {
        /* Цвет кузова */
        const colorHex = colorNames[config.color] || '#6366f1';
        bodyMesh.material.color.set(colorHex);

        /* Высота подвески */
        const susLevels = { 11: 0, 12: -0.1, 13: -0.18, 14: 0.08, 15: -0.25 };
        carGroup.position.y = susLevels[config.suspension] ?? 0;

        updateStats();
    }

    /* ===== STATS ===== */
    const statBase = { speed: 50, handling: 50, weight: 50 };

    function getPartStats(partId) {
        for (const cat in PARTS_DATA) {
            const p = PARTS_DATA[cat]?.find(x => x.id == partId);
            if (p) return { speed: p.stat_speed || 0, handling: p.stat_handling || 0, weight: p.stat_weight || 0 };
        }
        return { speed: 0, handling: 0, weight: 0 };
    }

    function updateStats() {
        let s = { ...statBase };
        ['body','wheels','suspension'].forEach(cat => {
            if (config[cat]) {
                const ps = getPartStats(config[cat]);
                s.speed    = Math.min(99, s.speed    + ps.speed);
                s.handling = Math.min(99, s.handling + ps.handling);
                s.weight   = Math.min(99, s.weight   - ps.weight);
            }
        });
        document.getElementById('statSpeed').style.width    = s.speed    + '%';
        document.getElementById('statHandling').style.width = s.handling + '%';
        document.getElementById('statWeight').style.width   = Math.max(0,s.weight)   + '%';
        document.getElementById('statSpeedVal').textContent    = s.speed;
        document.getElementById('statHandlingVal').textContent = s.handling;
        document.getElementById('statWeightVal').textContent   = Math.max(0,s.weight);
    }

    /* ===== PARTS PANEL ===== */
    function renderPartsPanel(cat) {
        const panel = document.getElementById('partsPanel');
        panel.innerHTML = '';
        const parts = PARTS_DATA[cat] || [];
        parts.forEach(part => {
            const div = document.createElement('div');
            div.className = 'as-part-card' + (config[cat] == part.id ? ' active' : '');
            div.dataset.partId = part.id;

            let thumbHtml = '';
            if (cat === 'color' && part.color_hex) {
                thumbHtml = '<div class="as-color-swatch" style="background:' + part.color_hex + ';width:44px;height:44px;border-radius:50%;flex-shrink:0"></div>';
            } else {
                thumbHtml = '<div class="as-part-card__thumb">' + (part.thumbnail ? '<img src="'+part.thumbnail+'">' : getCatIcon(cat)) + '</div>';
            }

            const price = part.price > 0 ? formatPrice(part.price) + ' ₽' : 'Базовая';
            div.innerHTML = thumbHtml +
                '<div class="as-part-card__info">' +
                  '<div class="as-part-card__name">' + escHtml(part.name) + '</div>' +
                  '<div class="as-part-card__price">' + price + '</div>' +
                '</div>';

            div.addEventListener('click', () => selectPart(cat, part.id));
            panel.appendChild(div);
        });
    }

    function selectPart(cat, id) {
        config[cat] = id;
        document.querySelectorAll('.as-part-card').forEach(c => {
            c.classList.toggle('active', c.dataset.partId == id);
        });
        applyConfig();
    }

    /* Category switching */
    document.querySelectorAll('.as-cat-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.as-cat-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCat = this.dataset.cat;
            renderPartsPanel(currentCat);
        });
    });

    /* Init */
    renderPartsPanel(currentCat);
    applyConfig();

    /* ===== SAVE ===== */
    document.getElementById('saveBuildBtn')?.addEventListener('click', () => openModal('saveModal'));

    document.getElementById('confirmSaveBtn')?.addEventListener('click', async () => {
        const name = document.getElementById('buildNameInput').value.trim() || 'Моя сборка';
        const carModelId = document.getElementById('carModelSelect').value;

        /* Снимок экрана */
        renderer.render(scene, camera);
        const screenshot = canvas.toDataURL('image/png');

        const payload = {
            build_id: BUILD_ID,
            name,
            car_model_id: carModelId || null,
            config,
            screenshot,
        };

        const btn = document.getElementById('confirmSaveBtn');
        btn.disabled = true;
        btn.textContent = 'Сохраняю...';

        try {
            const res = await fetch('/constructor/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                closeModal('saveModal');
                alert('✅ Сборка сохранена! Найди её в гараже.');
            } else {
                alert('Ошибка: ' + JSON.stringify(data.errors || data.error));
            }
        } catch(e) {
            alert('Ошибка сети');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Сохранить';
        }
    });

    /* Screenshot download */
    document.getElementById('screenshotBtn')?.addEventListener('click', () => {
        renderer.render(scene, camera);
        const link = document.createElement('a');
        link.download = 'autosphere-build.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });

    /* ===== HELPERS ===== */
    const catIcons = { body:'🚗', wheels:'⭕', suspension:'🔧', spoiler:'🔝', color:'🎨', interior:'💺' };
    function getCatIcon(cat) { return catIcons[cat] || '⚙️'; }
    function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function formatPrice(n) { return Number(n).toLocaleString('ru-RU'); }

})();
JS, \yii\web\View::POS_END);
?>