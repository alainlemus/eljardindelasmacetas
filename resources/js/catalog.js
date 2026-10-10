// Efectos del catálogo: burbujas de fondo (se pueden reventar), confeti, aparición al hacer
// scroll y parallax del hero. Todo en JS puro, sin dependencias.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const PALETTE = ['#4C9A3F', '#F6B93B', '#D93A35', '#A85A2E', '#7FC8F8', '#F58FB3', '#3A7F30'];
const store = {
    get: (k) => { try { return localStorage.getItem(k); } catch { return null; } },
    set: (k, v) => { try { localStorage.setItem(k, v); } catch { /* sin almacenamiento */ } },
};

let fxEnabled = !reduceMotion && store.get('fx') !== 'off';

// ───────────── Confeti ─────────────
let confettiCanvas = null;
let confettiCtx = null;
let pieces = [];
let confettiRaf = null;

function ensureConfettiCanvas() {
    if (confettiCanvas) return;
    confettiCanvas = document.createElement('canvas');
    confettiCanvas.id = 'fx-confetti';
    confettiCanvas.setAttribute('aria-hidden', 'true');
    document.body.appendChild(confettiCanvas);
    confettiCtx = confettiCanvas.getContext('2d');
    resizeConfetti();
    window.addEventListener('resize', resizeConfetti);
}

function resizeConfetti() {
    if (!confettiCanvas) return;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    confettiCanvas.width = window.innerWidth * dpr;
    confettiCanvas.height = window.innerHeight * dpr;
    confettiCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
}

function makePiece(x, y, { spread = Math.PI * 2, angle = -Math.PI / 2, speed = 9, fall = false } = {}) {
    const a = angle + (Math.random() - 0.5) * spread;
    const s = speed * (0.45 + Math.random() * 0.75);
    return {
        x, y,
        vx: fall ? (Math.random() - 0.5) * 2.2 : Math.cos(a) * s,
        vy: fall ? 1.5 + Math.random() * 2.5 : Math.sin(a) * s,
        w: 6 + Math.random() * 7,
        h: 4 + Math.random() * 6,
        rot: Math.random() * Math.PI,
        vr: (Math.random() - 0.5) * 0.35,
        color: PALETTE[(Math.random() * PALETTE.length) | 0],
        round: Math.random() < 0.3,
        life: 0,
        max: 140 + Math.random() * 80,
        wobble: Math.random() * Math.PI * 2,
    };
}

function stepConfetti() {
    const w = window.innerWidth;
    const h = window.innerHeight;
    confettiCtx.clearRect(0, 0, w, h);

    pieces = pieces.filter((p) => p.life < p.max && p.y < h + 30 && p.x > -30 && p.x < w + 30);
    for (const p of pieces) {
        p.life++;
        p.vy += 0.23;           // gravedad
        p.vx *= 0.985;          // fricción
        p.vy *= 0.992;
        p.wobble += 0.12;
        p.x += p.vx + Math.sin(p.wobble) * 0.6;
        p.y += p.vy;
        p.rot += p.vr;

        confettiCtx.save();
        confettiCtx.globalAlpha = Math.min(1, (p.max - p.life) / 40);
        confettiCtx.translate(p.x, p.y);
        confettiCtx.rotate(p.rot);
        confettiCtx.fillStyle = p.color;
        if (p.round) {
            confettiCtx.beginPath();
            confettiCtx.arc(0, 0, p.w / 2, 0, Math.PI * 2);
            confettiCtx.fill();
        } else {
            confettiCtx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
        }
        confettiCtx.restore();
    }

    if (pieces.length) {
        confettiRaf = requestAnimationFrame(stepConfetti);
    } else {
        confettiRaf = null;
        confettiCtx.clearRect(0, 0, w, h);
    }
}

function runConfetti() {
    if (!confettiRaf) confettiRaf = requestAnimationFrame(stepConfetti);
}

/** Explosión de confeti desde un punto de la pantalla. */
export function confettiBurst(x, y, count = 70) {
    if (!fxEnabled) return;
    ensureConfettiCanvas();
    for (let i = 0; i < count; i++) pieces.push(makePiece(x, y, { spread: Math.PI * 1.1, speed: 11 }));
    pieces = pieces.slice(-400);
    runConfetti();
}

/** Lluvia de confeti desde arriba durante unos segundos. */
export function confettiRain(ms = 2200) {
    if (!fxEnabled) return;
    ensureConfettiCanvas();
    const end = performance.now() + ms;
    const tick = () => {
        if (performance.now() > end || !fxEnabled) return;
        for (let i = 0; i < 4; i++) pieces.push(makePiece(Math.random() * window.innerWidth, -12, { fall: true }));
        pieces = pieces.slice(-400);
        runConfetti();
        setTimeout(tick, 60);
    };
    tick();
}

// ───────────── Burbujas de fondo ─────────────
let bubbleCanvas = null;
let bubbleCtx = null;
let bubbles = [];
let pops = [];
let bubbleRaf = null;

const bubbleColors = [
    [76, 154, 63], [246, 185, 59], [127, 200, 248], [245, 143, 179], [217, 58, 53],
];

function newBubble(initial = false) {
    const r = 7 + Math.random() * 24;
    return {
        x: Math.random() * window.innerWidth,
        y: initial ? Math.random() * window.innerHeight : window.innerHeight + r + Math.random() * 120,
        r,
        vy: 0.25 + Math.random() * 0.65,
        sway: Math.random() * Math.PI * 2,
        swaySpeed: 0.008 + Math.random() * 0.012,
        swayAmp: 0.25 + Math.random() * 0.5,
        color: bubbleColors[(Math.random() * bubbleColors.length) | 0],
        alpha: 0.16 + Math.random() * 0.18,
    };
}

function resizeBubbles() {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    bubbleCanvas.width = window.innerWidth * dpr;
    bubbleCanvas.height = window.innerHeight * dpr;
    bubbleCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
}

function bubbleCount() {
    return Math.max(8, Math.min(22, Math.round(window.innerWidth / 70)));
}

function stepBubbles() {
    const w = window.innerWidth;
    const h = window.innerHeight;
    bubbleCtx.clearRect(0, 0, w, h);

    while (bubbles.length < bubbleCount()) bubbles.push(newBubble());

    for (let i = bubbles.length - 1; i >= 0; i--) {
        const b = bubbles[i];
        b.y -= b.vy;
        b.sway += b.swaySpeed;
        b.x += Math.sin(b.sway) * b.swayAmp;

        if (b.y < -b.r * 2) {
            bubbles[i] = newBubble();
            continue;
        }

        const [r, g, bl] = b.color;
        const grad = bubbleCtx.createRadialGradient(b.x - b.r * 0.35, b.y - b.r * 0.35, b.r * 0.1, b.x, b.y, b.r);
        grad.addColorStop(0, `rgba(255,255,255,${b.alpha + 0.25})`);
        grad.addColorStop(0.45, `rgba(${r},${g},${bl},${b.alpha * 0.45})`);
        grad.addColorStop(1, `rgba(${r},${g},${bl},${b.alpha})`);
        bubbleCtx.beginPath();
        bubbleCtx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
        bubbleCtx.fillStyle = grad;
        bubbleCtx.fill();
        bubbleCtx.lineWidth = 1;
        bubbleCtx.strokeStyle = `rgba(${r},${g},${bl},${Math.min(0.5, b.alpha + 0.15)})`;
        bubbleCtx.stroke();
    }

    // Salpicaduras de las burbujas reventadas
    pops = pops.filter((p) => p.life < 22);
    for (const p of pops) {
        p.life++;
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.12;
        bubbleCtx.beginPath();
        bubbleCtx.arc(p.x, p.y, Math.max(0.5, p.r * (1 - p.life / 22)), 0, Math.PI * 2);
        bubbleCtx.fillStyle = `rgba(${p.c[0]},${p.c[1]},${p.c[2]},${0.5 * (1 - p.life / 22)})`;
        bubbleCtx.fill();
    }

    bubbleRaf = requestAnimationFrame(stepBubbles);
}

function startBubbles() {
    if (bubbleCanvas) {
        bubbleCanvas.style.display = '';
        if (!bubbleRaf) bubbleRaf = requestAnimationFrame(stepBubbles);
        return;
    }
    bubbleCanvas = document.createElement('canvas');
    bubbleCanvas.id = 'fx-bubbles';
    bubbleCanvas.setAttribute('aria-hidden', 'true');
    document.body.prepend(bubbleCanvas);
    bubbleCtx = bubbleCanvas.getContext('2d');
    resizeBubbles();
    window.addEventListener('resize', resizeBubbles);
    bubbles = Array.from({ length: bubbleCount() }, () => newBubble(true));
    bubbleRaf = requestAnimationFrame(stepBubbles);
}

function stopBubbles() {
    if (bubbleRaf) cancelAnimationFrame(bubbleRaf);
    bubbleRaf = null;
    if (bubbleCanvas) bubbleCanvas.style.display = 'none';
}

/** Revienta (con salpicadura) la burbuja que esté bajo el clic o toque. */
function popBubbleAt(x, y) {
    if (!fxEnabled || !bubbleCanvas) return;
    for (let i = 0; i < bubbles.length; i++) {
        const b = bubbles[i];
        if (Math.hypot(b.x - x, b.y - y) <= b.r + 8) {
            for (let k = 0; k < 9; k++) {
                const a = (Math.PI * 2 * k) / 9 + Math.random() * 0.5;
                pops.push({ x: b.x, y: b.y, vx: Math.cos(a) * (1.2 + Math.random() * 1.8), vy: Math.sin(a) * (1.2 + Math.random() * 1.8) - 0.8, r: 2 + Math.random() * 3, life: 0, c: b.color });
            }
            bubbles[i] = newBubble();
            return;
        }
    }
}

// ───────────── Interacciones ─────────────
function setupReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (reduceMotion || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                io.unobserve(e.target);
            }
        }
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    items.forEach((el) => io.observe(el));
}

function setupParallax() {
    const hero = document.querySelector('[data-parallax]');
    if (!hero || reduceMotion) return;
    const layers = hero.querySelectorAll('[data-depth]');
    hero.addEventListener('pointermove', (e) => {
        const r = hero.getBoundingClientRect();
        const nx = (e.clientX - r.left) / r.width - 0.5;
        const ny = (e.clientY - r.top) / r.height - 0.5;
        layers.forEach((el) => {
            const d = Number(el.dataset.depth) || 10;
            el.style.setProperty('--px', `${(-nx * d).toFixed(1)}px`);
            el.style.setProperty('--py', `${(-ny * d).toFixed(1)}px`);
        });
    });
    hero.addEventListener('pointerleave', () => {
        layers.forEach((el) => { el.style.setProperty('--px', '0px'); el.style.setProperty('--py', '0px'); });
    });
}

function setupClicks() {
    document.addEventListener('click', (e) => {
        const t = e.target instanceof Element ? e.target.closest('[data-confetti]') : null;
        if (t) {
            const r = t.getBoundingClientRect();
            confettiBurst(r.left + r.width / 2, r.top + r.height / 2, t.dataset.confetti === 'big' ? 120 : 60);
            if (t.classList.contains('logo-bob')) {
                t.classList.remove('is-wiggling');
                void t.offsetWidth; // reinicia la animación
                t.classList.add('is-wiggling');
                setTimeout(() => t.classList.remove('is-wiggling'), 800);
            }
        }
        popBubbleAt(e.clientX, e.clientY);
    });
}

function setupHeader() {
    const header = document.querySelector('header');
    if (!header) return;
    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

function setupToggle() {
    const btn = document.getElementById('fx-toggle');
    if (!btn) return;
    const apply = () => {
        btn.setAttribute('aria-pressed', String(fxEnabled));
        btn.title = fxEnabled ? 'Desactivar burbujas y confeti' : 'Activar burbujas y confeti';
    };
    apply();
    btn.addEventListener('click', () => {
        fxEnabled = !fxEnabled;
        store.set('fx', fxEnabled ? 'on' : 'off');
        if (fxEnabled) {
            startBubbles();
            confettiBurst(btn.getBoundingClientRect().left + 20, btn.getBoundingClientRect().top, 50);
        } else {
            stopBubbles();
            pieces = [];
        }
        apply();
    });
}

function init() {
    setupReveal();
    setupParallax();
    setupClicks();
    setupHeader();
    setupToggle();

    if (fxEnabled) {
        startBubbles();
        // Lluvia de confeti de bienvenida, una vez por sesión y solo en la portada.
        let seen = null;
        try { seen = sessionStorage.getItem('fx-welcome'); } catch { /* sin almacenamiento */ }
        if (!seen && document.querySelector('[data-welcome]')) {
            try { sessionStorage.setItem('fx-welcome', '1'); } catch { /* sin almacenamiento */ }
            setTimeout(() => confettiRain(2200), 700);
        }
    }
}

// Pausa las burbujas si la pestaña no se ve (ahorra batería).
document.addEventListener('visibilitychange', () => {
    if (document.hidden) { if (bubbleRaf) { cancelAnimationFrame(bubbleRaf); bubbleRaf = null; } }
    else if (fxEnabled && bubbleCanvas) { bubbleRaf = bubbleRaf || requestAnimationFrame(stepBubbles); }
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();
