<?php
/** @var yii\web\View $this */
/** @var app\models\MapPoint[] $points */
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use app\models\MapPoint;

$pointsData = array_map(fn($p) => $p->toArray(), $points);
$params     = Yii::$app->params;
$isGuest    = Yii::$app->user->isGuest;
?>
    <div class="as-map-page">

        <!-- Сайдбар -->
        <div class="as-map-sidebar">
            <div class="as-map-sidebar__head">
                <h2>📍 События и места</h2>
                <div class="as-map-filter-btns">
                    <button class="as-map-filter-btn active" data-type="">Все</button>
                    <?php foreach (MapPoint::TYPES as $type => $label): ?>
                        <button class="as-map-filter-btn" data-type="<?= $type ?>">
                            <?= MapPoint::TYPE_ICONS[$type] ?> <?= $label ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="as-map-sidebar__list" id="pointsList">
                <?php foreach ($points as $point): ?>
                    <div class="as-map-point-item" data-id="<?= $point->id ?>" data-type="<?= $point->type ?>"
                         onclick="flyToPoint(<?= $point->lat ?>, <?= $point->lng ?>)">
                        <div class="as-map-point-item__type">
                            <?= $point->getTypeIcon() ?> <?= $point->getTypeLabel() ?>
                        </div>
                        <div class="as-map-point-item__name"><?= Html::encode($point->name) ?></div>
                        <?php if ($point->description): ?>
                            <div class="as-map-point-item__desc"><?= Html::encode(mb_strimwidth($point->description, 0, 70, '…')) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Карта -->
        <div style="flex:1;position:relative">
            <div id="mapContainer" style="width:100%;height:100%"></div>

            <?php if (!$isGuest): ?>
                <button class="as-btn as-btn--primary as-map-add-btn" onclick="openModal('addPointModal')">
                    + Добавить точку
                </button>
            <?php else: ?>
                <a href="<?= Url::to(['/auth/login']) ?>" class="as-btn as-btn--ghost as-map-add-btn">
                    🔒 Войди, чтобы добавить
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Модалка добавления точки -->
    <div class="as-modal-overlay" id="addPointModal">
        <div class="as-modal">
            <div class="as-modal__head">
                <span class="as-modal__title">Добавить точку</span>
                <button class="as-modal__close" onclick="closeModal('addPointModal')">×</button>
            </div>
            <div class="as-form" id="addPointForm">
                <div class="as-form-group">
                    <label class="as-form-label">Тип</label>
                    <select class="as-form-input" id="ptType">
                        <?php foreach (MapPoint::TYPES as $type => $label): ?>
                            <option value="<?= $type ?>"><?= MapPoint::TYPE_ICONS[$type] ?> <?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="as-form-group">
                    <label class="as-form-label">Название</label>
                    <input type="text" class="as-form-input" id="ptName" placeholder="Название места...">
                </div>
                <div class="as-form-group">
                    <label class="as-form-label">Описание</label>
                    <textarea class="as-form-textarea" id="ptDesc" rows="3" placeholder="Расскажи подробнее..."></textarea>
                </div>
                <div class="as-form-group">
                    <label class="as-form-label">Координаты (нажми на карту)</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" class="as-form-input" id="ptLat" placeholder="Широта" readonly>
                        <input type="text" class="as-form-input" id="ptLng" placeholder="Долгота" readonly>
                    </div>
                    <span style="font-size:.75rem;color:var(--text-3);margin-top:4px;display:block">
                    Кликни на карту, чтобы выбрать место
                </span>
                </div>
                <button class="as-btn as-btn--primary as-btn--full" id="savePointBtn">Отправить на проверку</button>
            </div>
        </div>
    </div>

<?php
$pointsJson = Json::encode($pointsData);
$defaultLat = $params['mapDefaultLat'];
$defaultLng = $params['mapDefaultLng'];
$defaultZoom = $params['mapDefaultZoom'];
$csrf = Yii::$app->request->csrfToken;

$this->registerJs(<<<JS
(function() {
    /* ===== INIT MAP ===== */
    const map = L.map('mapContainer', { zoomControl: true }).setView([$defaultLat, $defaultLng], $defaultZoom);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CartoDB',
        maxZoom: 19
    }).addTo(map);

    /* ===== POINTS DATA ===== */
    const allPoints = $pointsJson;
    const markers   = {};

    function makeIcon(color) {
        return L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;border-radius:50%;background:' + color + ';border:2px solid #fff;box-shadow:0 0 8px ' + color + '"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });
    }

    function renderPoints(filterType) {
        Object.values(markers).forEach(m => map.removeLayer(m));
        const list = document.getElementById('pointsList');
        list.querySelectorAll('.as-map-point-item').forEach(el => {
            const show = !filterType || el.dataset.type === filterType;
            el.style.display = show ? '' : 'none';
        });

        allPoints.forEach(p => {
            if (filterType && p.type !== filterType) return;
            const marker = L.marker([p.lat, p.lng], { icon: makeIcon(p.color) })
                .addTo(map)
                .bindPopup('<div style="min-width:180px;font-family:Inter,sans-serif">' +
                    '<strong style="font-size:.95rem">' + p.icon + ' ' + p.name + '</strong>' +
                    '<div style="color:#888;font-size:.75rem;margin:4px 0">' + p.label + ' · ' + p.user + '</div>' +
                    (p.desc ? '<div style="font-size:.8rem">' + p.desc + '</div>' : '') +
                '</div>');
            markers[p.id] = marker;
        });
    }

    renderPoints('');

    /* ===== FILTER ===== */
    window.addEventListener('mapFilter', e => renderPoints(e.detail || ''));

    /* ===== FLY TO ===== */
    window.flyToPoint = function(lat, lng) {
        map.flyTo([lat, lng], 15, { duration: 1.2 });
        const m = Object.values(markers).find(mk => {
            const ll = mk.getLatLng();
            return Math.abs(ll.lat - lat) < 0.0001 && Math.abs(ll.lng - lng) < 0.0001;
        });
        if (m) setTimeout(() => m.openPopup(), 1300);
    };

    /* ===== PICK COORD ON MAP ===== */
    let pickedLat = null, pickedLng = null;
    map.on('click', e => {
        pickedLat = e.latlng.lat.toFixed(7);
        pickedLng = e.latlng.lng.toFixed(7);
        document.getElementById('ptLat').value = pickedLat;
        document.getElementById('ptLng').value = pickedLng;
    });

    /* ===== SAVE POINT ===== */
    document.getElementById('savePointBtn')?.addEventListener('click', async () => {
        const name = document.getElementById('ptName').value.trim();
        if (!name)       { alert('Введи название'); return; }
        if (!pickedLat)  { alert('Выбери место на карте'); return; }

        const body = {
            type:        document.getElementById('ptType').value,
            name,
            description: document.getElementById('ptDesc').value,
            lat:         pickedLat,
            lng:         pickedLng,
        };

        const res  = await fetch('/map/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '$csrf', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) {
            closeModal('addPointModal');
            alert('✅ Точка отправлена на проверку модератору!');
        } else {
            alert('Ошибка: ' + JSON.stringify(data.errors));
        }
    });
})();
JS, \yii\web\View::POS_END);
?>