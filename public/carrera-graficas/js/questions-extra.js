/* questions-extra.js — Preguntas adicionales para ampliar el juego.
   Agrega más entradas a los arrays IDENTIFY, PREDICT y CHOOSE de questions.js.
   No toca graphs.js: reutiliza las gráficas ya definidas. */

(function () {
  // ✅ Verificamos que Questions esté cargado con los arrays expuestos
  if (typeof Questions === 'undefined' || !Questions.IDENTIFY) {
    console.warn('questions-extra.js: Questions no tiene los arrays expuestos. Revisa questions.js.');
    return;
  }

  // ============================================================
  // 1. MÁS PREGUNTAS DE "IDENTIFICA"
  //    Cada entrada es { kind: 'xt' | 'vt', type: '...' }
  //    Los tipos válidos son los que ya existen en graphs.js
  // ============================================================
  Questions.IDENTIFY.push(
    { kind: 'xt', type: 'mru' },        // MRU (otra ronda, otra redacción automática)
    { kind: 'xt', type: 'mru_neg' },    // MRU en sentido contrario
    { kind: 'xt', type: 'accel' },      // MRUV acelerado
    { kind: 'xt', type: 'decel' },      // MRUV desacelerado
    { kind: 'xt', type: 'rest' },       // Reposo
    { kind: 'vt', type: 'vconst' },     // Velocidad constante
    { kind: 'vt', type: 'vpos' },       // Aceleración (v-t)
    { kind: 'vt', type: 'vneg' },       // Frenado (v-t)
    { kind: 'vt', type: 'vrest' }       // Reposo (v-t)
  );

  // ============================================================
  // 2. MÁS PREGUNTAS DE "PREDICE"
  //    Cada entrada es { kind: 'xt' | 'vt', type: '...' }
  // ============================================================
  Questions.PREDICT.push(
    { kind: 'xt', type: 'mru' },        // MRU: seguirá constante
    { kind: 'xt', type: 'accel' },      // Aceleración: seguirá aumentando
    { kind: 'xt', type: 'decel' },      // Frenado: seguirá disminuyendo
    { kind: 'xt', type: 'rest' },       // Reposo: seguirá en reposo
    { kind: 'vt', type: 'vconst' },     // Velocidad constante
    { kind: 'vt', type: 'vpos' },       // Aceleración
    { kind: 'vt', type: 'vneg' },       // Frenado
    { kind: 'vt', type: 'vrest' }       // Reposo
  );

  // ============================================================
  // 3. MÁS PREGUNTAS DE "ELIGE LA GRÁFICA"
  //    Cada entrada es { kind, text, correct, wrong: [...] }
  //    "correct" y "wrong" deben ser tipos válidos en graphs.js
  // ============================================================
  Questions.CHOOSE.push(
    // --- Variantes de posición-tiempo (xt) ---
    {
      kind: 'xt',
      text: 'El automóvil avanza con velocidad constante y luego se detiene.',
      correct: 'rest',
      wrong: ['mru', 'decel']
    },
    {
      kind: 'xt',
      text: 'El automóvil retrocede con velocidad constante.',
      correct: 'mru_neg',
      wrong: ['mru', 'rest']
    },
    {
      kind: 'xt',
      text: 'El automóvil aumenta su velocidad de forma uniforme.',
      correct: 'accel',
      wrong: ['mru', 'decel']
    },
    {
      kind: 'xt',
      text: 'El automóvil reduce su velocidad hasta detenerse.',
      correct: 'decel',
      wrong: ['accel', 'mru']
    },
    {
      kind: 'xt',
      text: 'El automóvil permanece estacionado todo el tiempo.',
      correct: 'rest',
      wrong: ['mru', 'mru_neg']
    },
    {
      kind: 'xt',
      text: 'El automóvil se mueve alejándose con velocidad constante.',
      correct: 'mru',
      wrong: ['mru_neg', 'accel']
    },

    // --- Variantes de velocidad-tiempo (vt) ---
    {
      kind: 'vt',
      text: 'El automóvil mantiene una velocidad constante de 4 m/s.',
      correct: 'vconst',
      wrong: ['vpos', 'vneg']
    },
    {
      kind: 'vt',
      text: 'El automóvil acelera de forma uniforme desde el reposo.',
      correct: 'vpos',
      wrong: ['vconst', 'vneg']
    },
    {
      kind: 'vt',
      text: 'El automóvil frena con aceleración negativa constante.',
      correct: 'vneg',
      wrong: ['vpos', 'vconst']
    },
    {
      kind: 'vt',
      text: 'El automóvil está detenido y no se mueve.',
      correct: 'vrest',
      wrong: ['vconst', 'vpos']
    },
    {
      kind: 'vt',
      text: 'El automóvil viaja a velocidad constante sin acelerar.',
      correct: 'vconst',
      wrong: ['vpos', 'vrest']
    },
    {
      kind: 'vt',
      text: 'El automóvil aumenta su velocidad cada segundo.',
      correct: 'vpos',
      wrong: ['vconst', 'vrest']
    }
  );

  // ✅ Mensaje en consola para confirmar que se cargaron las preguntas extra
  console.log(
    '✅ questions-extra.js cargado. Preguntas totales → ' +
    'Identifica: ' + Questions.IDENTIFY.length + ' | ' +
    'Predice: ' + Questions.PREDICT.length + ' | ' +
    'Elige: ' + Questions.CHOOSE.length
  );
})();