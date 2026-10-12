/* questions.js — banco de preguntas y armado de la partida.
   Cada ronda se devuelve ya normalizada para que game.js solo la dibuje. */
const Questions = (() => {
  const shuffle = (a) => {
    const r = [...a];
    for (let i = r.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [r[i], r[j]] = [r[j], r[i]];
    }
    return r;
  };
  const pick = (arr, n) => shuffle(arr).slice(0, n);

  // Repetición espaciada: los tipos que más se fallan salen con más frecuencia
  const FK = 'cg_fails';
  const loadFails = () => { try { return JSON.parse(localStorage.getItem(FK)) || {}; } catch (e) { return {}; } };
  function noteResult(key, ok) {
    const f = loadFails();
    f[key] = ok ? Math.max((f[key] || 0) - 1, 0) : (f[key] || 0) + 1;
    try { localStorage.setItem(FK, JSON.stringify(f)); } catch (e) { /* sin almacenamiento */ }
  }
  const wpick = (arr, n, keyOf) => {
    const f = loadFails();
    return arr.map((i) => ({ i, s: Math.random() * (1 + 2 * (f[keyOf(i)] || 0)) }))
      .sort((a, b) => b.s - a.s).slice(0, n).map((o) => o.i);
  };

  // Información de cada tipo de desafío
  const META = {
    identify: { emoji: '🟢', name: 'Identifica' },
    predict:  { emoji: '🟡', name: 'Predice' },
    choose:   { emoji: '🔴', name: 'Elige la gráfica' },
  };

  // Nombre del movimiento según el tipo de gráfica
  const LABEL = {
    xt: {
      rest: 'Reposo',
      mru: 'MRU',
      mru_neg: 'MRU en sentido contrario',
      accel: 'MRUV acelerado',
      decel: 'MRUV desacelerado',
    },
    vt: {
      vconst: 'MRU',
      vpos: 'MRUV acelerado',
      vneg: 'MRUV desacelerado',
      vrest: 'Reposo',
    },
  };

  // Explicación breve de cada gráfica
  const WHY = {
    rest: 'La posición no cambia: la recta es horizontal, el móvil está en reposo.',
    mru: 'La posición crece en línea recta: la pendiente (velocidad) es constante y positiva.',
    mru_neg: 'La posición disminuye en línea recta: velocidad constante, pero en sentido contrario.',
    accel: 'La curva es cada vez más empinada: la velocidad aumenta (MRUV acelerado).',
    decel: 'La curva es cada vez más plana: la velocidad disminuye hasta detenerse (MRUV desacelerado).',
    vconst: 'La velocidad no cambia: recta horizontal y aceleración cero (MRU).',
    vpos: 'La velocidad crece linealmente: aceleración positiva constante (MRUV acelerado).',
    vneg: 'La velocidad decrece linealmente: aceleración negativa (MRUV desacelerado).',
    vrest: 'La velocidad es cero todo el tiempo: el móvil está en reposo.',
  };

  // ---------- 1. Identifica ----------
  const IDENTIFY = [
    ...Object.keys(LABEL.xt).map((type) => ({ kind: 'xt', type })),
    ...Object.keys(LABEL.vt).map((type) => ({ kind: 'vt', type })),
  ];

  function buildIdentify(item) {
    const labels = LABEL[item.kind];
    const others = Object.keys(labels).filter((t) => t !== item.type);
    const types = shuffle([item.type, ...pick(others, 3)]);
    return {
      type: 'identify',
      key: item.kind + ':' + item.type, topic: labels[item.type], tip: WHY[item.type],
      prompt: '¿Qué movimiento representa?',
      sub: '',
      graph: { kind: item.kind, type: item.type },
      options: types.map((t) => ({ label: labels[t], correct: t === item.type })),
      explain: WHY[item.type],
    };
  }

  // ---------- 2. Predice ----------
  // effect: lo que ocurre con la velocidad después del tramo observado
  const EFFECT = {
    rest: 'rest', mru: 'const', accel: 'up', decel: 'down',
    vconst: 'const', vpos: 'up', vneg: 'down', vrest: 'rest',
  };
  const PREDICT = [
    { kind: 'xt', type: 'rest' }, { kind: 'xt', type: 'mru' },
    { kind: 'xt', type: 'accel' }, { kind: 'xt', type: 'decel' },
    { kind: 'vt', type: 'vconst' }, { kind: 'vt', type: 'vpos' },
    { kind: 'vt', type: 'vneg' }, { kind: 'vt', type: 'vrest' },
  ];
  const PREDICT_OPTIONS = {
    up: 'Aumentará la velocidad',
    down: 'Disminuirá la velocidad',
    const: 'Permanecerá constante',
    rest: 'Permanecerá en reposo',
  };
  const PREDICT_WHY = {
    up: 'La tendencia indica que la velocidad sigue creciendo: el auto recorrerá cada vez más distancia por segundo.',
    down: 'La tendencia indica que la velocidad sigue bajando: el auto frenará hasta detenerse.',
    const: 'La velocidad no cambia: el auto mantiene el mismo ritmo.',
    rest: 'No hay movimiento: el auto seguirá detenido.',
  };

  function buildPredict(item) {
    const effect = EFFECT[item.type];
    return {
      type: 'predict',
      key: item.kind + ':' + item.type, topic: LABEL[item.kind][item.type], tip: WHY[item.type],
      prompt: '¿Qué ocurrirá después?',
      sub: 'La gráfica muestra los primeros 6 segundos del movimiento.',
      graph: { kind: item.kind, type: item.type },
      effect,
      options: shuffle(Object.keys(PREDICT_OPTIONS)).map((k) => ({
        label: PREDICT_OPTIONS[k],
        correct: k === effect,
      })),
      explain: PREDICT_WHY[effect],
    };
  }

  // ---------- 3. Elige la gráfica ----------
  const CHOOSE = [
    { kind: 'xt', text: 'El automóvil parte del reposo y aumenta uniformemente su velocidad.', correct: 'accel', wrong: ['mru', 'decel'] },
    { kind: 'xt', text: 'El automóvil está estacionado, sin moverse.', correct: 'rest', wrong: ['mru', 'accel'] },
    { kind: 'xt', text: 'El automóvil se aleja del punto de partida con velocidad constante.', correct: 'mru', wrong: ['accel', 'mru_neg'] },
    { kind: 'xt', text: 'El automóvil regresa hacia el punto de partida con velocidad constante.', correct: 'mru_neg', wrong: ['mru', 'rest'] },
    { kind: 'xt', text: 'El automóvil frena de manera uniforme hasta detenerse.', correct: 'decel', wrong: ['accel', 'mru'] },
    { kind: 'vt', text: 'El automóvil parte del reposo y su velocidad aumenta uniformemente.', correct: 'vpos', wrong: ['vconst', 'vneg'] },
    { kind: 'vt', text: 'El automóvil frena con aceleración negativa constante.', correct: 'vneg', wrong: ['vpos', 'vconst'] },
    { kind: 'vt', text: 'El automóvil se desplaza con velocidad constante.', correct: 'vconst', wrong: ['vpos', 'vrest'] },
  ];

  function buildChoose(item) {
    const name = item.kind === 'xt' ? 'posición – tiempo' : 'velocidad – tiempo';
    const types = shuffle([item.correct, ...item.wrong]);
    return {
      type: 'choose',
      key: item.kind + ':' + item.correct, topic: LABEL[item.kind][item.correct], tip: WHY[item.correct],
      prompt: '🚗 ' + item.text,
      sub: `Elige la gráfica de ${name} que lo representa.`,
      options: types.map((t) => ({
        graph: { kind: item.kind, type: t },
        correct: t === item.correct,
      })),
      explain: WHY[item.correct],
    };
  }

  // ---------- Partida ----------
  // 6 rondas: 2 de cada tipo, sin repetir el mismo tipo dos veces seguidas
  function sequence() {
    const base = ['identify', 'identify', 'predict', 'predict', 'choose', 'choose'];
    let s;
    do { s = shuffle(base); } while (s.some((t, i) => i > 0 && t === s[i - 1]));
    return s;
  }

  function buildGame() {
    const queues = {
      identify: wpick(IDENTIFY, 2, (i) => i.kind + ':' + i.type).map(buildIdentify),
      predict: wpick(PREDICT, 2, (i) => i.kind + ':' + i.type).map(buildPredict),
      choose: wpick(CHOOSE, 2, (i) => i.kind + ':' + i.correct).map(buildChoose),
    };
    return sequence().map((t) => queues[t].shift());
  }

  return { META, buildGame, noteResult, IDENTIFY, PREDICT, CHOOSE, buildIdentify, buildPredict, buildChoose, LABEL, WHY, EFFECT, PREDICT_OPTIONS, PREDICT_WHY };
})();
