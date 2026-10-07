(() => {
    'use strict';

    const WALK_MS = [8000, 12000];   // cada 8–12 s pasa a reposo
    const REST_MS = [3000, 5000];    // descansa 3–5 s
    const EAT_MS = 2000;             // comer al hacer click
    const SAVE_EVERY_MS = 1000;
    const STATES = ['walking', 'resting', 'eating'];

    const rand = (min, max) => min + Math.random() * (max - min);
    const stem = (file) => file.replace(/\.[^.]+$/, '');

    class Pet {
        constructor(def, layer, ctx) {
            this.def = def;
            this.layer = layer;
            this.ctx = ctx;
            this.size = def.size ?? 64;
            this.speed = def.speed ?? 50;
            this.key = `pet:${ctx.userId}:${def.id}`;

            this.bubbleOpen = false;
            this.pendingBubble = false;
            this.lastPhrase = -1;
            this.currentSrc = '';
            this.sinceSave = 0;

            this.build();
            this.preload();
            this.restore();
        }

        /* ---------- DOM ---------- */
        build() {
            this.el = document.createElement('div');
            this.el.className = 'pet';
            this.el.style.width = this.el.style.height = `${this.size}px`;
            this.el.title = this.def.name;

            this.img = document.createElement('img');
            this.img.alt = this.def.name;
            this.img.draggable = false;

            this.bubble = document.createElement('div');
            this.bubble.className = 'pet-bubble';

            this.el.append(this.bubble, this.img);
            this.el.addEventListener('click', () => this.onClick());
            this.layer.appendChild(this.el);
        }

        preload() {
            const files = Object.values(this.def.animations).flatMap((a) =>
                typeof a === 'string' ? [a] : Object.values(a)
            );
            files.forEach((f) => { new Image().src = `${this.ctx.basePath}/${f}`; });
        }

        /* ---------- Persistencia ---------- */
        load() {
            try { return JSON.parse(localStorage.getItem(this.key)); } catch { return null; }
        }

        save() {
            try {
                localStorage.setItem(this.key, JSON.stringify({
                    petId: this.def.id,
                    x: Math.round(this.x),
                    direction: this.dir,
                    state: this.state,
                    animation: this.animation,
                    updatedAt: Math.floor(Date.now() / 1000),
                }));
            } catch { /* modo privado / cuota llena */ }
        }

        restore() {
            const s = this.load();
            this.dir = s?.direction === -1 ? -1 : 1;
            this.x = Math.min(Math.max(s?.x ?? rand(0, this.maxX()), 0), this.maxX());

            let state = STATES.includes(s?.state) ? s.state : 'walking';
            if (state === 'eating') state = 'walking'; // eating es transitorio
            this.setState(state);
            this.applyPosition();
        }

        /* ---------- Estados ---------- */
        setState(state, ms) {
            this.state = state;
            this.timeLeft = ms ?? (
                state === 'walking' ? rand(...WALK_MS) :
                state === 'resting' ? rand(...REST_MS) : EAT_MS
            );
            this.render();
            this.save();
        }

        nextState() {
            if (this.state === 'walking') return this.setState('resting');

            if (this.state === 'eating' && this.pendingBubble) {
                this.pendingBubble = false;
                this.openBubble();
                return;
            }
            this.setState('walking');
        }

        has(state) { return Boolean(this.def.animations[state]); }

        /* ---------- Animación ---------- */
        resolve() {
            const entry = this.def.animations[this.state] ?? this.def.animations.walking;

            if (typeof entry === 'string') {
                const facing = this.def.faces === 'left' ? -1 : 1;
                return { file: entry, flip: this.dir !== facing };
            }
            return { file: this.dir === 1 ? entry.right : entry.left, flip: false };
        }

        render() {
            const { file, flip } = this.resolve();
            const src = `${this.ctx.basePath}/${file}`;

            if (src !== this.currentSrc) {          // evita reiniciar el GIF sin necesidad
                this.img.src = src;
                this.currentSrc = src;
            }
            this.img.style.transform = flip ? 'scaleX(-1)' : '';
            this.animation = stem(file);
            this.el.dataset.state = this.state;
        }

        /* ---------- Movimiento ---------- */
        maxX() { return Math.max(0, this.layer.clientWidth - this.size); }

        applyPosition() {
            this.el.style.transform = `translate3d(${this.x}px, 0, 0)`;
        }

        move(dt) {
            const max = this.maxX();
            this.x += this.dir * this.speed * (dt / 1000);

            if (this.x <= 0)   { this.x = 0;   this.dir = 1;  this.render(); }
            if (this.x >= max) { this.x = max; this.dir = -1; this.render(); }

            this.applyPosition();
        }

        onResize() {
            this.x = Math.min(Math.max(this.x, 0), this.maxX());
            this.applyPosition();
            if (this.bubbleOpen) this.positionBubble();
        }

        tick(dt) {
            if (!this.bubbleOpen) {
                this.timeLeft -= dt;
                if (this.state === 'walking') this.move(dt);
                if (this.timeLeft <= 0) this.nextState();
            }

            this.sinceSave += dt;
            if (this.sinceSave >= SAVE_EVERY_MS) { this.sinceSave = 0; this.save(); }
        }

        /* ---------- Interacción ---------- */
        onClick() {
            if (this.bubbleOpen) return this.closeBubble();
            if (this.state === 'eating') return;

            if (this.has('eating')) {
                this.pendingBubble = true;          // primero come 2 s, luego el cartel
                this.setState('eating', EAT_MS);
            } else {
                this.openBubble();
            }
        }

        openBubble() {
            const phrases = this.ctx.phrases;
            let i;
            do { i = Math.floor(Math.random() * phrases.length); }
            while (phrases.length > 1 && i === this.lastPhrase);
            this.lastPhrase = i;

            this.bubble.textContent = phrases[i];
            this.bubbleOpen = true;
            this.setState('resting', REST_MS[1]);
            this.bubble.classList.add('is-open');
            this.positionBubble();
        }

        closeBubble() {
            this.bubbleOpen = false;
            this.bubble.classList.remove('is-open');
            this.setState('walking');
        }

        /** Evita que el cartel se salga por los bordes del contenedor. */
        positionBubble() {
            this.bubble.style.setProperty('--shift', '0px');
            const b = this.bubble.getBoundingClientRect();
            const s = this.layer.getBoundingClientRect();
            let shift = 0;
            if (b.left < s.left) shift = s.left - b.left;
            else if (b.right > s.right) shift = s.right - b.right;
            this.bubble.style.setProperty('--shift', `${shift}px`);
        }
    }

    /* ---------- Arranque ---------- */
    function init() {
        const layer = document.querySelector('[data-pet-layer]');
        if (!layer) return;

        const ctx = JSON.parse(layer.dataset.config);
        const pets = ctx.pets.map((def) => new Pet(def, layer, ctx));

        let last = performance.now();
        const loop = (now) => {
            const dt = Math.min(now - last, 100);   // evita saltos al volver de otra pestaña
            last = now;
            pets.forEach((p) => p.tick(dt));
            requestAnimationFrame(loop);
        };
        requestAnimationFrame(loop);

        new ResizeObserver(() => pets.forEach((p) => p.onResize())).observe(layer);

        const saveAll = () => pets.forEach((p) => p.save());
        window.addEventListener('pagehide', saveAll);
        document.addEventListener('visibilitychange', () => { if (document.hidden) saveAll(); });
    }

    document.addEventListener('DOMContentLoaded', init);
})();