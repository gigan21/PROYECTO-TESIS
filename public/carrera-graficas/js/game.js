/* game.js — lógica de la partida: rondas, tiempo, respuestas y progreso. */
const Game = (() => {
  const ROUNDS = 6;
  const TIME = 15;        // segundos por ronda
  const STEP = 100 / 5;   // cada acierto avanza 20 %: con 5 aciertos se llega a la meta

  const $ = (id) => document.getElementById(id);
  // ✅ NUEVO: Se agregó 'failsCount' para contar los fallos acumulados
  const S = { rounds: [], i: 0, hits: 0, failsCount: 0, progress: 0, locked: true, timer: null, deadline: 0, results: [], fails: [] };
  const wait = (ms) => new Promise((r) => setTimeout(r, ms));
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function show(id) {
    document.querySelectorAll('.screen').forEach((s) => s.classList.toggle('active', s.id === id));
  }

  // ---------- Flujo ----------
  function start() {
    if (typeof Chart === 'undefined') { $('start-error').hidden = false; return; }
    // ✅ Se reinicia 'failsCount' a 0
    Object.assign(S, { rounds: Questions.buildGame(), i: 0, hits: 0, failsCount: 0, progress: 0, results: [], fails: [] });
    Animation.resetCar();
    show('screen-game');
    showRound();
  }

  function showRound() {
    const r = S.rounds[S.i];
    S.locked = false;
    Graphs.destroyAll();
    const ch = $('challenge');
    ch.dataset.type = r.type;
    ch.classList.remove('fade-in'); void ch.offsetWidth; ch.classList.add('fade-in');
    renderDots();
    Animation.hideDemo();

    const meta = Questions.META[r.type];
    $('round-label').textContent = `Ronda ${S.i + 1} / ${ROUNDS}`;
    $('badge').textContent = `${meta.emoji} ${meta.name}`;
    $('badge').dataset.type = r.type;
    $('prompt').textContent = r.prompt;
    $('sub').textContent = r.sub;
    $('feedback').hidden = true;
    $('next-btn').hidden = true;

    const box = $('graph-box');
    const opts = $('options');
    box.innerHTML = '';
    opts.innerHTML = '';
    opts.className = 'options' + (r.type === 'choose' ? ' graphs' : '');

    if (r.type === 'choose') {
      // Tres gráficas como opciones
      box.hidden = true;
      r.options.forEach((o, idx) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'opt graph-opt';
        b.setAttribute('aria-label', `Opción ${idx + 1}`);
        b.innerHTML = '<div class="mini"><canvas></canvas></div>';
        b.addEventListener('click', () => answer(idx));
        opts.append(b);

        // 🔗 FIX — Esperar a que el canvas tenga tamaño real
        Graphs.renderWhenReady(b.querySelector('canvas'), o.graph, { compact: true });
      });
    } else {
      // Una gráfica + opciones de texto
      box.hidden = false;
      box.innerHTML = '<canvas></canvas>';

      // 🔗 FIX — Esperar a que el canvas tenga tamaño real antes de iniciar el dot
      Graphs.renderWhenReady(box.querySelector('canvas'), r.graph, { partial: r.type === 'predict' })
        .then(() => {
          // Solo iniciar el punto animado cuando el chart está listo
          Graphs.runDot(TIME * 1000);
        });

      r.options.forEach((o, idx) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'opt';
        b.textContent = o.label;
        b.addEventListener('click', () => answer(idx));
        opts.append(b);
      });
    }

    startTimer();
    // 🔗 runDot se llama en el .then() del render de arriba (no duplicar aquí)
  }

  function renderDots() {
    $('dots').innerHTML = Array.from({ length: ROUNDS }, (_, k) => {
      const st = k < S.results.length ? (S.results[k] ? 'ok' : 'bad') : k === S.i ? 'now' : '';
      return `<i class="dot ${st}"></i>`;
    }).join('');
    const lb = $('round-label');
    lb.classList.remove('pop'); void lb.offsetWidth; lb.classList.add('pop');
  }

  async function answer(idx) {          // idx === null → se acabó el tiempo
    if (S.locked) return;
    S.locked = true;
    stopTimer();
    Graphs.stopDot();

    const r = S.rounds[S.i];
    const timeout = idx === null;
    const right = !timeout && r.options[idx].correct;

    // Marca la respuesta correcta y, si falló, la elegida
    [...$('options').children].forEach((b, k) => {
      b.disabled = true;
      if (r.options[k].correct) b.classList.add('right');
      else if (k === idx) b.classList.add('wrong');
    });

    // Retroalimentación inmediata
    let text = r.explain;
    if (!right) {
      const hint = r.type === 'choose'
        ? 'La correcta es la gráfica marcada en verde.'
        : `Respuesta correcta: ${r.options.find((o) => o.correct).label}.`;
      text = `${hint} ${r.explain}`;
    }
    showFeedback(right ? 'ok' : timeout ? 'time' : 'bad', text,
      right ? '🚗 ¡Tu auto avanza!' : '🛑 Tu auto se detiene.');

    S.results.push(right);
    Questions.noteResult(r.key, right);
    if (!right) S.fails.push({ topic: r.topic, tip: r.tip });
    renderDots();

    // "Predice": la curva se dibuja de izquierda a derecha y se anima la consecuencia
    if (r.type === 'predict') {
      await Graphs.revealAll();
      if (!right) Graphs.highlight();
      await Animation.demo(r.effect);
    } else if (!right) Graphs.highlight();

    // Progreso en la pista
    if (right) {
      S.hits++;
      S.progress = Math.min(100, S.progress + STEP);
      await Animation.moveCar(S.progress);
    } else {
      // ✅ NUEVA LÓGICA: Contar fallos y explotar al llegar a 4
      S.failsCount++;
      Animation.stallCar();
      
      // Esperar a que termine el tambaleo antes de decidir si explota
      await wait(reduced ? 0 : 750); 
      
      if (S.failsCount >= 4) {
        $('fb-race').textContent = '💥 ¡Tu auto explotó! Fallaste 4 veces.';
        await Animation.explode();
        
        // ✅ Termina el juego inmediatamente
        Graphs.destroyAll();
        await wait(reduced ? 0 : 500);
        finish();
        return; // Detiene la ejecución aquí
      }
    }

    const last = S.i === ROUNDS - 1;
    const next = $('next-btn');
    next.textContent = last ? 'Ver resultado' : 'Siguiente ronda';
    next.hidden = false;
    next.focus();
  }

  async function next() {
    const ch = $('challenge');
    ch.classList.add('fade-out');
    await wait(reduced ? 0 : 250);
    ch.classList.remove('fade-out');
    S.i++;
    if (S.i >= ROUNDS) finish(); else showRound();
  }

  function finish() {
    Graphs.destroyAll();
    // ✅ Estrellas: si explotó (4 fallos), 0 estrellas. Si no, según aciertos.
    const stars = S.failsCount >= 4 ? 0 : S.hits >= 5 ? 3 : S.hits >= 3 ? 2 : S.hits >= 1 ? 1 : 0;
    $('stars').textContent = '⭐'.repeat(stars) + '☆'.repeat(3 - stars);
    $('score').textContent = `${S.hits} / ${ROUNDS} aciertos`;
    
    // ✅ Mensaje final adaptado a la nueva lógica
    $('end-msg').textContent = S.failsCount >= 4
      ? '💥 ¡Tu auto explotó! Fallaste 4 veces. ¡Inténtalo otra vez!'
      : S.progress >= 100
        ? '🏆 ¡Llegaste a la meta! Interpretas las gráficas como un piloto experto.'
        : `Tu auto recorrió el ${Math.round(S.progress)} % de la pista y no alcanzó la meta. Necesitas 5 aciertos: ¡puedes intentarlo nuevamente!`;
    
    // Mini-resumen de errores (sin repetir el mismo tema)
    const seen = new Set();
    const rev = $('review');
    rev.innerHTML = '';
    S.fails.filter((f) => !seen.has(f.topic) && seen.add(f.topic)).forEach((f) => {
      const li = document.createElement('li');
      const b = document.createElement('b');
      b.textContent = `Fallaste en ${f.topic}: `;
      li.append(b, f.tip);
      rev.append(li);
    });
    rev.hidden = !rev.children.length;
    show('screen-end');
  }

  // ---------- Retroalimentación ----------
  function showFeedback(state, text, race) {
    const fb = $('feedback');
    fb.hidden = false;
    fb.className = 'feedback ' + (state === 'ok' ? 'ok' : 'bad');
    $('fb-title').textContent = state === 'ok' ? '¡Correcto! 🎉'
      : state === 'time' ? '⏱ ¡Se acabó el tiempo!' : 'Incorrecto';
    $('fb-text').textContent = text;
    $('fb-race').textContent = race;
  }

  // ---------- Temporizador (15 s) ----------
  function startTimer() {
    stopTimer();
    S.deadline = Date.now() + TIME * 1000;
    paintTime(TIME, TIME * 1000);
    S.timer = setInterval(() => {
      const ms = Math.max(S.deadline - Date.now(), 0);
      paintTime(Math.ceil(ms / 1000), ms);
      if (ms <= 0) answer(null);
    }, 100);
  }
  function stopTimer() { clearInterval(S.timer); }
  function paintTime(sec, ms) {
    $('time').textContent = sec;
    $('timebar-fill').style.width = (ms / (TIME * 1000)) * 100 + '%';
    $('time').parentElement.classList.toggle('low', sec <= 5);
  }

  // ---------- Inicio ----------
  function init() {
    $('start-btn').addEventListener('click', start);
    $('again-btn').addEventListener('click', start);
    $('next-btn').addEventListener('click', next);
  }
  document.addEventListener('DOMContentLoaded', init);

  return { start };
})();