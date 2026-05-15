/* ============================================================
   AutoSphere 3D — Main JS (app.js)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ========== NAVBAR scroll effect ========== */
    const nav = document.getElementById('asNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        });
    }

    /* ========== BURGER MENU ========== */
    const burger = document.getElementById('navBurger');
    const navLinks = document.getElementById('navLinks');
    if (burger && navLinks) {
        burger.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    /* ========== AUTO-DISMISS FLASH ========== */
    document.querySelectorAll('.as-flash').forEach(el => {
        setTimeout(() => el.remove(), 5000);
    });

    /* ========== MODAL HELPERS ========== */
    window.openModal = function (id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('open');
    };
    window.closeModal = function (id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('open');
    };
    // Close on overlay click
    document.querySelectorAll('.as-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('open');
        });
    });

    /* ========== CAROUSEL ========== */
    document.querySelectorAll('.as-carousel').forEach(carousel => {
        const track = carousel.querySelector('.as-carousel__track');
        const prevBtn = carousel.querySelector('.as-carousel__btn--prev');
        const nextBtn = carousel.querySelector('.as-carousel__btn--next');
        if (!track) return;

        let pos = 0;
        const itemW = 320 + 24; // width + gap
        const total = track.children.length;

        function updateCarousel() {
            const max = Math.max(0, total - Math.floor(carousel.offsetWidth / itemW));
            pos = Math.max(0, Math.min(pos, max));
            track.style.transform = `translateX(-${pos * itemW}px)`;
        }

        if (prevBtn) prevBtn.addEventListener('click', () => { pos--; updateCarousel(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { pos++; updateCarousel(); });
        window.addEventListener('resize', updateCarousel);
    });

    /* ========== HOME RIBBON duplicate for infinite scroll ========== */
    const ribbonTrack = document.querySelector('.as-ribbon__track');
    if (ribbonTrack) {
        ribbonTrack.innerHTML += ribbonTrack.innerHTML;
    }

    /* ========== LIKE BUTTON ========== */
    document.querySelectorAll('.as-like-btn[data-build]').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.build;
            const csrf = document.querySelector('meta[name=csrf-token]');
            if (!csrf) return;
            const res = await fetch(`/gallery/like/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf.content, 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                this.classList.toggle('liked', data.liked);
                const counter = this.querySelector('.as-like-count');
                if (counter) counter.textContent = data.count;
            }
        });
    });

    /* ========== GALLERY FILTERS ========== */
    const filterForm = document.getElementById('galleryFilterForm');
    if (filterForm) {
        filterForm.querySelectorAll('select, input').forEach(el => {
            el.addEventListener('change', () => filterForm.submit());
        });
    }

    /* ========== CHAT POLLING ========== */
    const chatLog = document.getElementById('chatLog');
    if (chatLog) {
        let lastId = chatLog.dataset.lastId || 0;

        async function pollChat() {
            try {
                const res = await fetch(`/chat/poll?last_id=${lastId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.messages && data.messages.length) {
                        data.messages.forEach(msg => {
                            appendChatMessage(msg);
                            lastId = msg.id;
                        });
                        chatLog.scrollTop = chatLog.scrollHeight;
                    }
                }
            } catch (e) {}
            setTimeout(pollChat, 3000);
        }

        // Initial scroll to bottom
        chatLog.scrollTop = chatLog.scrollHeight;
        setTimeout(pollChat, 3000);

        function appendChatMessage(msg) {
            const div = document.createElement('div');
            div.className = `as-msg${msg.is_own ? ' as-msg--own' : ''}`;
            div.dataset.msgId = msg.id;
            div.innerHTML = `
        <div class="as-msg__avatar">${msg.avatar_letter}</div>
        <div class="as-msg__body">
          <div class="as-msg__head">
            <span class="as-msg__name as-msg__name--${msg.role}">${escHtml(msg.username)}</span>
            <span class="as-msg__time">${msg.time}</span>
          </div>
          <div class="as-msg__text">${escHtml(msg.text)}</div>
          ${msg.can_delete ? `<div class="as-msg__actions"><button class="as-msg__action-btn" onclick="deleteMsg(${msg.id})">Удалить</button>${msg.can_ban ? `<button class="as-msg__action-btn" onclick="banUser(${msg.user_id})">Бан</button>` : ''}</div>` : ''}
        </div>`;
            chatLog.appendChild(div);
        }

        /* Chat send */
        const chatForm = document.getElementById('chatForm');
        if (chatForm) {
            chatForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const input = this.querySelector('#chatInput');
                const text = input.value.trim();
                if (!text) return;
                const csrf = document.querySelector('meta[name=csrf-token]');
                await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': csrf?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ text })
                });
                input.value = '';
            });
        }
    }

    /* ========== MAP FILTER BUTTONS ========== */
    document.querySelectorAll('.as-map-filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.as-map-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const type = this.dataset.type;
            window.dispatchEvent(new CustomEvent('mapFilter', { detail: type }));
        });
    });
});

/* ========== HELPERS ========== */
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function deleteMsg(id) {
    if (!confirm('Удалить сообщение?')) return;
    const csrf = document.querySelector('meta[name=csrf-token]');
    await fetch(`/chat/delete/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf?.content || '', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const el = document.querySelector(`[data-msg-id="${id}"] .as-msg__text`);
    if (el) { el.textContent = '[Удалено]'; el.classList.add('as-msg__text--deleted'); }
}

async function banUser(userId) {
    if (!confirm('Забанить пользователя?')) return;
    const csrf = document.querySelector('meta[name=csrf-token]');
    const res = await fetch(`/chat/ban/${userId}`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf?.content || '', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    alert(data.message || 'Готово');
}