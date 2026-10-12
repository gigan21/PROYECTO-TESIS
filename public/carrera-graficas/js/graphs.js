/* graphs.js — gráficas con Chart.js (posición–tiempo y velocidad–tiempo).
   Los datos se calculan con ecuaciones físicas, así siempre son coherentes. */
const Graphs = (() => {
  const T_MAX = 10;   // duración total (s)
  const T_OBS = 6;    // tramo visible en el desafío "Predice" (s)
  const STEP = 0.25;

  // x(t) en metros
  const XT = {
    rest:    () => 20,
    mru:     (t) => 5 + 3 * t,
    mru_neg: (t) => 35 - 3 * t,
    accel:   (t) => 0.3 * t * t,          // a = +0.6 m/s², parte del reposo
    decel:   (t) => 6 * t - 0.3 * t * t,  // v0 = 6 m/s, a = -0.6 m/s², se detiene en t = 10
  };
  // v(t) en m/s
  const VT = {
    vconst: () => 4,
    vpos:   (t) => 0.6 * t,
    vneg:   (t) => 6 - 0.6 * t,
    vrest:  () => 0,
  };
  const FUNCS = { xt: XT, vt: VT };

  // Ejes fijos para que la forma de cada gráfica sea siempre comparable
  const AXIS = {
    xt: { title: 'Posición – tiempo', y: 'x (m)', min: 0, max: 40, step: 10 },
    vt: { title: 'Velocidad – tiempo', y: 'v (m/s)', min: -2, max: 8, step: 2 },
  };

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let charts = [];
  let dotRaf = 0;

  // Punto clave de cada gráfica (para anotar o resaltar la diferencia)
  const KEY = {
    decel: { t: 10, txt: 'v = 0 (se detiene)' }, vneg: { t: 10, txt: 'v = 0 (se detiene)' },
    accel: { t: 9, txt: 'cada vez más empinada' }, vpos: { t: 9, txt: 'v aumenta' },
    mru: { t: 6, txt: 'pendiente constante' }, mru_neg: { t: 6, txt: 'pendiente negativa' },
    rest: { t: 5, txt: 'x no cambia' }, vconst: { t: 5, txt: 'v constante' }, vrest: { t: 5, txt: 'v = 0' },
  };
  const AUTO = new Set(['decel', 'vneg']);   // siempre se anota "v = 0"

  // Plugin: zona futura oscurecida, punto móvil y anotaciones
  const overlay = {
    id: 'overlay',
    beforeDatasetsDraw(c) {
      if (!c.$partial || c.$revealed) return;
      const { ctx, chartArea: a, scales: { x } } = c;
      const x0 = x.getPixelForValue(T_OBS);
      ctx.save();
      ctx.fillStyle = 'rgba(228, 230, 235, 0.1)';
      ctx.fillRect(x0, a.top, a.right - x0, a.bottom - a.top);
      ctx.fillStyle = '#5b6472';
      ctx.font = 'bold 13px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText('¿Qué pasará aquí?', (x0 + a.right) / 2, a.top + 20);
      ctx.restore();
    },
    afterDatasetsDraw(c) {
      if (c.$compact) return;
      const { ctx, chartArea: a, scales: { x, y } } = c;
      ctx.save();
      if (c.$dotT != null) {   // punto que recorre la curva
        ctx.beginPath();
        ctx.arc(x.getPixelForValue(c.$dotT), y.getPixelForValue(c.$fn(c.$dotT)), 7, 0, 7);
        ctx.fillStyle = '#2b6be8'; ctx.fill();
        ctx.lineWidth = 3; ctx.strokeStyle = '#fff'; ctx.stroke();
      }
      const visible = !c.$partial || c.$revealed;
      if (visible && (AUTO.has(c.$spec.type) || c.$force)) {   // anotación del punto clave
        const k = KEY[c.$spec.type];
        const px = x.getPixelForValue(k.t), py = y.getPixelForValue(c.$fn(k.t));
        ctx.beginPath(); ctx.arc(px, py, 9, 0, 7);
        ctx.lineWidth = 3; ctx.strokeStyle = '#e0474c'; ctx.stroke();
        ctx.font = 'bold 13px sans-serif';
        const w = ctx.measureText(k.txt).width + 14;
        const bx = Math.min(px + 4, a.right) - w, by = Math.max(py - 36, a.top + 2);
        ctx.fillStyle = '#e0474c';
        ctx.fillRect(bx, by, w, 22);
        ctx.fillStyle = '#fff'; ctx.textAlign = 'left';
        ctx.fillText(k.txt, bx + 7, by + 16);
      }
      ctx.restore();
    },
  };

  function sample(fn, from, to) {
    const pts = [];
    for (let t = from; t <= to + 1e-9; t += STEP) {
      pts.push({ x: +t.toFixed(2), y: +fn(t).toFixed(3) });
    }
    return pts;
  }

  // opts.partial: solo muestra los primeros T_OBS segundos (se completa con reveal)
  // opts.compact: versión pequeña para el desafío "Elige la gráfica"
  function render(canvas, spec, opts = {}) {
    const { partial = false, compact = false } = opts;
    const ax = AXIS[spec.kind];
    const fn = FUNCS[spec.kind][spec.type];
    const fs = compact ? 10 : 12;
    const gridColor = (ctx) => (ctx.tick && ctx.tick.value === 0 ? '#7c8696' : '#e3e7ee');
    const gridWidth = (ctx) => (ctx.tick && ctx.tick.value === 0 ? 2 : 1);

    const chart = new Chart(canvas, {
      type: 'line',
      plugins: [overlay],
      data: {
        datasets: [
          { data: sample(fn, 0, partial ? T_OBS : T_MAX), borderColor: '#2b6be8', borderWidth: 3.5, pointRadius: 0,
            fill: spec.kind === 'vt' ? 'origin' : false, backgroundColor: 'rgba(43,107,232,0.16)' },
          { data: [], borderColor: '#ff7a1a', borderWidth: 3.5, borderDash: [7, 5], pointRadius: 0,
            fill: spec.kind === 'vt' ? 'origin' : false, backgroundColor: 'rgba(255,122,26,0.14)' }, // continuación
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: reduced ? 0 : 300 },
        events: [],
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false },
          title: { display: true, text: spec.kind === 'vt' && !compact ? ax.title + ' (el área = distancia)' : ax.title, color: '#8d07bd', font: { size: fs + 3, weight: '700' } },
        },
        scales: {
          x: {
            type: 'linear', min: 0, max: T_MAX,
            title: { display: true, text: 't (s)', color: '#e5e9ef', font: { size: fs + 3, weight: '700' } },
            ticks: { stepSize: 2, color: '#d7deea', font: { size: fs, weight: '700' } },
            grid: { color: gridColor, lineWidth: gridWidth },
          },
          y: {
            min: ax.min, max: ax.max,
            title: { display: true, text: ax.y, color: '#e2e6ee', font: { size: fs + 3, weight: '700' } },
            ticks: { stepSize: ax.step, color: '#dadfe6', font: { size: fs, weight: '700' } },
            grid: { color: gridColor, lineWidth: gridWidth },
          },
        },
      },
    });
    chart.$spec = spec;
    chart.$partial = partial;
    chart.$compact = compact;
    chart.$fn = fn;
    charts.push(chart);
    return chart;
  }

  // La continuación (naranja punteada) se dibuja de izquierda a derecha
  function revealAll() {
    return Promise.all(charts.filter((c) => c.$partial).map((c) => new Promise((resolve) => {
      const full = sample(c.$fn, T_OBS, T_MAX);
      const ms = reduced ? 0 : 1500, t0 = performance.now();
      (function frame(now) {
        const k = ms ? Math.min((now - t0) / ms, 1) : 1;
        c.data.datasets[1].data = full.slice(0, Math.max(2, Math.ceil(k * full.length)));
        c.update('none');
        if (k < 1) requestAnimationFrame(frame);
        else { c.$revealed = true; c.update('none'); resolve(); }
      })(t0);
    })));
  }

  // Punto azul que recorre la curva mientras corre el tiempo de la ronda
  function runDot(ms) {
    stopDot();
    const cs = charts.filter((c) => !c.$compact);
    if (!cs.length) return;
  
    const t0 = performance.now();
    (function frame(now) {
      const k = Math.min((now - t0) / ms, 1);
      cs.forEach((c) => { c.$dotT = k * (c.$partial ? T_OBS : T_MAX); c.draw(); });
      if (k < 1) dotRaf = requestAnimationFrame(frame);
    })(t0);
  }
  function stopDot() { cancelAnimationFrame(dotRaf); }

  // Tras fallar: marca el punto donde se ve la diferencia
  function highlight() {
    charts.filter((c) => !c.$compact).forEach((c) => { c.$force = true; c.draw(); });
  }

  function destroyAll() {
    stopDot();
    charts.forEach((c) => c.destroy());
    charts = [];
  }

  // 🔗 FIX — Espera a que el canvas tenga tamaño real antes de crear el Chart
function renderWhenReady(canvas, spec, opts = {}) {
  return new Promise((resolve) => {
    const tryRender = () => {
      if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
        const c = render(canvas, spec, opts);
        resolve(c);
      } else {
        requestAnimationFrame(tryRender);
      }
    };
    tryRender();
  });
}

return {
  render,
  renderWhenReady,   // 🔗 nuevo
  revealAll,
  runDot,
  stopDot,
  highlight,
  destroyAll
};
})();
