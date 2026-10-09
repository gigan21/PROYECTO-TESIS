/* game.js — Lógica principal: 4 niveles con progresión real. */
const Game = (() => {
  const $ = (id) => document.getElementById(id);

  const r1 = (x) => Math.round(x * 10) / 10;
  const r2 = (x) => Math.round(x * 100) / 100;
  const clamp = (x, a, b) => Math.min(b, Math.max(a, x));
  const azar = (min, max, paso) => {
    const n = Math.round((max - min) / paso);
    return r2(min + Math.floor(Math.random() * (n + 1)) * paso);
  };
  const DEC = { d: 1, h: 2, t: 2, v: 1, v0: 1, a: 1, g: 2 };
  const dato = (k, s, n, v, u) => ({ k, s, n, v, u, dec: DEC[k] ?? 2 });

  function programar(ms, fn) {
    const sesion = Estado.sesion;
    setTimeout(() => { if (sesion === Estado.sesion) fn(); }, ms);
  }

  function mapFromDatos(datos) {
    const m = {};
    datos.forEach((x) => { m[x.k] = x.v; });
    return m;
  }

  function vibrar(patron) {
    try {
      if (navigator.vibrate) navigator.vibrate(patron);
    } catch { /* noop */ }
  }

  function tipoError(pct, acierto) {
    if (acierto) return '';
    if (pct > 50) return 'Error de concepto';
    if (pct >= 10) return 'Error de cálculo';
    return 'Casi. Revisa los decimales';
  }

  function toleranciaNivel(correcta, unidad, rel) {
    const base = unidad === 's' ? CONFIG.TOLERANCIA_BASE_S : CONFIG.TOLERANCIA_BASE;
    return Math.max(base, rel * Math.abs(correcta));
  }

  function multiplicadorRacha() {
    if (Estado.racha >= CONFIG.RACHA_MULT_3) return CONFIG.MULT_RACHA_3;
    if (Estado.racha >= CONFIG.RACHA_MULT_2) return CONFIG.MULT_RACHA_2;
    return 1;
  }

  function emitirParticulasLanzamiento() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const o = Fisica.puntoLanzamiento();
    for (let i = 0; i < 12; i++) {
      Estado.particulas.push({
        x: o.x,
        y: o.y,
        vx: 80 + Math.random() * 120,
        vy: -40 - Math.random() * 80,
        life: 0.4 + Math.random() * 0.3,
        t0: Estado.tiempo,
        color: '#00f0ff',
      });
    }
  }

  function registrarOndaImpacto(exito) {
    const j = Estado.jeringa;
    Estado.ondaImpacto = {
      x: j.x,
      y: j.y,
      t0: Estado.tiempo,
      color: exito ? '#00ff88' : '#ff3355',
    };
  }

  const FORMULAS = [
    {
      id: 'mru-v', grupo: 'MRU', magnitud: 'v', unidad: 'm/s',
      texto: 'v = d / t',
      prepara: (e) => [dato('d', 'd', 'distancia', r1(e.d), 'm'), dato('t', 't', 'tiempo de vuelo', r2(e.t), 's')],
      calcula: (m) => m.d / m.t,
      pasos(m) {
        const v = m.d / m.t;
        return ['v = d / t', `v = ${m.d} / ${m.t}`, `v = ${r1(v)} m/s`];
      },
    },
    {
      id: 'mru-d', grupo: 'MRU', magnitud: 'd', unidad: 'm',
      texto: 'd = v · t',
      prepara: (e) => [dato('v', 'v', 'velocidad', r1(e.v0), 'm/s'), dato('t', 't', 'tiempo de vuelo', r2(e.t), 's')],
      calcula: (m) => m.v * m.t,
      pasos(m) {
        const d = m.v * m.t;
        return ['d = v · t', `d = ${m.v} · ${m.t}`, `d = ${r1(d)} m`];
      },
    },
    {
      id: 'mru-t', grupo: 'MRU', magnitud: 't', unidad: 's',
      texto: 't = d / v',
      prepara: (e) => [dato('d', 'd', 'distancia', r1(e.d), 'm'), dato('v', 'v', 'velocidad', r1(e.v0), 'm/s')],
      calcula: (m) => m.d / m.v,
      pasos(m) {
        const t = m.d / m.v;
        return ['t = d / v', `t = ${m.d} / ${m.v}`, `t = ${r2(t)} s`];
      },
    },
    {
      id: 'mruv-vf', grupo: 'MRUV', magnitud: 'vf', unidad: 'm/s',
      texto: 'vf = v₀ + a·t',
      prepara: (e) => {
        const v0 = r1(e.v0 * azar(0.2, 0.45, 0.05));
        const t = azar(0.6, 1.4, 0.1);
        const a = r1((e.v0 - v0) / t);
        return [dato('v0', 'v₀', 'velocidad inicial', v0, 'm/s'), dato('a', 'a', 'aceleración', a, 'm/s²'), dato('t', 't', 'tiempo', t, 's')];
      },
      calcula: (m) => m.v0 + m.a * m.t,
      pasos(m) {
        const vf = m.v0 + m.a * m.t;
        return ['vf = v₀ + a·t', `vf = ${m.v0} + ${m.a} · ${m.t}`, `vf = ${r1(vf)} m/s`];
      },
    },
    {
      id: 'mruv-d', grupo: 'MRUV', magnitud: 'd', unidad: 'm',
      texto: 'd = v₀·t + ½·a·t²',
      prepara: (e) => {
        const v0 = r1(e.v0 * azar(0.2, 0.45, 0.05));
        const t = azar(0.6, 1.4, 0.1);
        const a = r1((e.v0 - v0) / t);
        return [dato('v0', 'v₀', 'velocidad inicial', v0, 'm/s'), dato('a', 'a', 'aceleración', a, 'm/s²'), dato('t', 't', 'tiempo', t, 's')];
      },
      calcula: (m) => m.v0 * m.t + 0.5 * m.a * m.t * m.t,
      pasos(m) {
        const d = m.v0 * m.t + 0.5 * m.a * m.t * m.t;
        return ['d = v₀·t + ½·a·t²', `d = ${m.v0}·${m.t} + 0.5·${m.a}·${m.t}²`, `d = ${r1(d)} m`];
      },
    },
    {
      id: 'cl-t', grupo: 'Caída libre', magnitud: 't', unidad: 's',
      texto: 't = √(2h / g)',
      prepara: (e) => [dato('h', 'h', 'altura', r2(e.h), 'm'), dato('g', 'g', 'gravedad', e.g, 'm/s²')],
      calcula: (m) => Math.sqrt(2 * m.h / m.g),
      pasos(m) {
        const t = Math.sqrt(2 * m.h / m.g);
        return ['t = √(2h / g)', `t = √(2·${m.h} / ${m.g})`, `t = ${r2(t)} s`];
      },
    },
    {
      id: 'cl-v', grupo: 'Caída libre', magnitud: 'v', unidad: 'm/s',
      texto: 'v = g · t',
      prepara: (e) => [dato('g', 'g', 'gravedad', e.g, 'm/s²'), dato('t', 't', 'tiempo de caída', r2(e.t), 's')],
      calcula: (m) => m.g * m.t,
      pasos(m) {
        const v = m.g * m.t;
        return ['v = g · t', `v = ${m.g} · ${m.t}`, `v = ${r1(v)} m/s`];
      },
    },
    {
      id: 'cl-h', grupo: 'Caída libre', magnitud: 'h', unidad: 'm',
      texto: 'h = ½ · g · t²',
      prepara: (e) => [dato('g', 'g', 'gravedad', e.g, 'm/s²'), dato('t', 't', 'tiempo de caída', r2(e.t), 's')],
      calcula: (m) => 0.5 * m.g * m.t * m.t,
      pasos(m) {
        const h = 0.5 * m.g * m.t * m.t;
        return ['h = ½ · g · t²', `h = 0.5 · ${m.g} · ${m.t}²`, `h = ${r2(h)} m`];
      },
    },
    {
      id: 'lan-v0', grupo: 'Lanzamiento', magnitud: 'v₀', unidad: 'm/s',
      texto: 'v₀ = d / t   (t = √(2h/g))',
      prepara: (e) => [dato('d', 'd', 'distancia', r1(e.d), 'm'), dato('h', 'h', 'altura', r2(e.h), 'm'), dato('g', 'g', 'gravedad', e.g, 'm/s²')],
      calcula: (m) => m.d / Math.sqrt(2 * m.h / m.g),
      pasos(m) {
        const t = Math.sqrt(2 * m.h / m.g);
        const v = m.d / t;
        return [
          't = √(2h/g)',
          `t = ${r2(t)} s`,
          'v₀ = d / t',
          `v₀ = ${m.d} / ${r2(t)}`,
          `v₀ = ${r1(v)} m/s`,
        ];
      },
    },
    {
      id: 'lan-t', grupo: 'Lanzamiento', magnitud: 't', unidad: 's',
      texto: 't = √(2h / g)   (tiempo de vuelo)',
      prepara: (e) => [dato('h', 'h', 'altura', r2(e.h), 'm'), dato('g', 'g', 'gravedad', e.g, 'm/s²')],
      calcula: (m) => Math.sqrt(2 * m.h / m.g),
      pasos(m) {
        const t = Math.sqrt(2 * m.h / m.g);
        return ['t = √(2h / g)', `t = √(2·${m.h} / ${m.g})`, `t = ${r2(t)} s`];
      },
    },
  ];

  function elegirFormula() {
    if (Estado.formulaReforzar) {
      const f = FORMULAS.find((x) => x.id === Estado.formulaReforzar);
      if (f) return f;
    }
    const candidatas = FORMULAS.filter((f) => f.id !== Estado.ultimaFormulaId);
    const f = candidatas[Math.floor(Math.random() * candidatas.length)];
    Estado.ultimaFormulaId = f.id;
    return f;
  }

  function aplicarPosiciones(pos) {
    Estado.medico.x = pos.medico.x;
    Estado.medico.y = pos.medico.y;
    Estado.lyra.x = pos.lyra.x;
    Estado.lyra.y = pos.lyra.y;
    Estado.lyra.enGlobo = pos.lyra.enGlobo;
    Estado.syncPacienteDesdeLyra();
  }

  function generarIntento() {
    const nivel = Estado.nivelConfig;
    const gRonda = CONFIG.GRAVEDAD * nivel.gravedad;
    const viento = nivel.viento;
    const o = Fisica.calcularVelocidadOptima({ g: gRonda, viento });
    const e = { d: o.dx, h: o.dy, t: o.t, v0: o.v0, g: gRonda };
    const formula = elegirFormula();
    if (!Estado.formulaReforzar) Estado.ultimaFormulaId = formula.id;
    const datos = formula.prepara(e);
    const m = mapFromDatos(datos);
    const correcta = formula.calcula(m);
    const tol = toleranciaNivel(correcta, formula.unidad, nivel.tolerancia);
    const factorZona = Niveles.factorZonaAcierto(nivel);
    const radioPx = Math.max(
      CONFIG.RADIO_LYRA_BASE_PX,
      Math.abs(o.dx) * CONFIG.ESCALA * (tol / Math.abs(correcta)),
    ) * factorZona;

    Estado.formula = formula;
    Estado.ronda = {
      nivelId: nivel.id,
      nombreNivel: nivel.nombre,
      formula,
      datos,
      correcta,
      tol,
      radioPx,
      viento,
      gRonda,
      flags: {
        viento: Math.abs(viento) > 0.01,
        gravedadAlta: nivel.gravedad > 1.01,
      },
      pasos: formula.pasos ? formula.pasos(m) : [],
    };
    Estado.pistaUsadaEnIntento = false;
  }

  function cargarNivel(num, opts = {}) {
    const nivel = Niveles.getNivel(num);
    Estado.nivelActual = num;
    Estado.nivelConfig = nivel;
    Estado.sueloY = nivel.sueloY;
    Estado.obstaculosInstanciados = Niveles.instanciarObstaculos(nivel);
    aplicarPosiciones(Niveles.posicionarActores(nivel));
    Estado.paciente.herido = true;
    Estado.syncPacienteDesdeLyra();
    Estado.fase = 'calculo';
    Fisica.reiniciar();
    generarIntento();
    UI.ocultarFeedback();
    UI.ocultarResolucion();
    UI.ocultarPista();
    UI.aplicarNivel(nivel, opts);
    UI.mostrarRonda(Estado.ronda, { transicion: opts.transicion });
    UI.actualizarHUD();
    UI.setNarrativa(opts.narrativa ?? nivel.narrativa);
  }

  function repetirNivel() {
    Estado.fase = 'calculo';
    aplicarPosiciones(Niveles.posicionarActores(Estado.nivelConfig));
    Fisica.reiniciar();
    generarIntento();
    UI.ocultarFeedback();
    UI.ocultarResolucion();
    UI.ocultarPista();
    UI.mostrarRonda(Estado.ronda, { transicion: true });
    UI.actualizarHUD();
    UI.setNarrativa(Narrativa.textoFalloNivel(Estado.nivelActual, Estado.dosisRestantes));
  }

  function iniciar() {
    Estado.sesion++;
    Estado.reiniciar();
    Estado.enJuego = true;
    UI.ocultarOverlays();
    UI.ocultarFeedback();
    UI.ocultarResolucion();
    UI.mostrarJuego();
    cargarNivel(1);
  }

  function verificarYLanzar() {
    if (!Estado.enJuego || Estado.fase !== 'calculo') return;

    const input = $('entrada-respuesta');
    const valor = parseFloat(String(input.value).replace(',', '.'));
    if (!isFinite(valor)) {
      UI.mostrarEstadoCalculo('Escribe un número válido para lanzar.', 'error');
      input.className = 'incorrecto';
      input.focus();
      return;
    }

    const r = Estado.ronda;
    const diff = Math.abs(valor - r.correcta);
    const aciertoCalculo = diff <= r.tol + 1e-9;
    Estado.ultimoIntento = {
      respuesta: valor,
      correcta: r.correcta,
      unidad: r.formula.unidad,
      aciertoCalculo,
      acierto: false,
      diferenciaAbs: diff,
      diferenciaPct: (diff / Math.abs(r.correcta)) * 100,
      tipoError: tipoError((diff / Math.abs(r.correcta)) * 100, aciertoCalculo),
    };
    Estado.ultimosPasos = r.pasos;

    const ratio = clamp(valor / r.correcta, 0.05, 4);
    const { v0 } = Fisica.calcularVelocidadOptima();

    Estado.fase = 'lanzamiento';
    UI.bloquearEntrada(true);
    UI.setLanzando(true);
    UI.mostrarEstadoCalculo('', '');
    emitirParticulasLanzamiento();
    Fisica.lanzar(v0 * ratio);
  }

  function alAterrizar() {
    Estado.fase = 'resultado';
    const intento = Estado.ultimoIntento;
    const impacto = Fisica.evaluarImpactoLyra();
    const choque = Estado.jeringa.choqueObstaculo;
    const acierto = intento.aciertoCalculo && impacto.aciertoFisico && !choque;

    intento.acierto = acierto;
    intento.aciertoFisico = impacto.aciertoFisico;
    intento.choqueObstaculo = choque;
    if (!impacto.aciertoFisico && intento.aciertoCalculo) {
      intento.tipoError = 'La jeringa no alcanzó a Lyra. Ajusta el cálculo.';
    }
    if (choque) {
      intento.tipoError = 'Chocaste la zona roja. El cálculo puede estar bien, pero la trayectoria no.';
    }

    Estado.jeringa.exitoso = acierto;
    registrarOndaImpacto(acierto);
    UI.setLanzando(false);
    UI.mostrarResolucion(Estado.ultimosPasos || []);
    if (acierto) procesarAcierto();
    else procesarFallo();
  }

  function marcarFormulaDominada(formula) {
    Estado.formulasDominadas.add(formula.texto);
    Estado.formulasRepasar.delete(formula.texto);
    if (Estado.formulaReforzar === formula.id) Estado.formulaReforzar = null;
  }

  function puntosNivelActual() {
    return CONFIG.PUNTOS_POR_NIVEL[Estado.nivelActual - 1] ?? 20;
  }

  function procesarAcierto() {
    const i = Estado.ultimoIntento;
    const r = Estado.ronda;
    Estado.intentos++;
    Estado.racha++;
    Estado.rachaMaxSesion = Math.max(Estado.rachaMaxSesion, Estado.racha);
    const mult = multiplicadorRacha();
    const pts = Math.round(puntosNivelActual() * mult);
    Estado.puntuacion = Math.min(CONFIG.PUNTOS_MAX, Estado.puntuacion + pts);
    marcarFormulaDominada(r.formula);
    Estado.paciente.herido = false;
    vibrar([50, 50, 100]);
    UI.marcarEntrada('correcto');
    UI.actualizarHUD();

    const esUltimo = Estado.nivelActual >= 4;
    if (esUltimo) {
      Estado.nivelesCompletados = 4;
      if (Estado.fallos === 0) {
        Estado.puntuacion = Math.min(CONFIG.PUNTOS_MAX, Estado.puntuacion + CONFIG.BONUS_SIN_FALLOS + CONFIG.BONUS_PERFECTA);
      }
      UI.setNarrativa(Narrativa.TEXTO_VICTORIA);
      UI.feedback({
        tipo: 'ok',
        titulo: '✓ LYRA CURADA',
        lineas: [`+${pts} pts (racha ×${mult})`, '¡Misión completa!'],
      });
      programar(CONFIG.T_FEEDBACK_ACIERTO, () => UI.mostrarPantallaFinal(true));
      return;
    }

    Estado.nivelesCompletados = Estado.nivelActual;
    const siguiente = Estado.nivelActual + 1;
    UI.setNarrativa(Narrativa.textoAvance(siguiente));
    UI.feedback({
      tipo: 'ok',
      titulo: '✓ DOSIS EN LYRA',
      lineas: [
        `+${pts} pts (racha ×${mult})`,
        Narrativa.nombreNivel(siguiente),
      ],
    });
    programar(CONFIG.T_FEEDBACK_ACIERTO, () => {
      cargarNivel(siguiente, { transicion: true, narrativa: Niveles.getNivel(siguiente).narrativa });
    });
  }

  function perderCorazon() {
    for (let idx = CONFIG.DOSIS_MAX - 1; idx >= 0; idx--) {
      if (!Estado.corazonesPerdidos[idx]) {
        Estado.corazonesPerdidos[idx] = true;
        Estado.corazonAnim = { indice: idx, t0: Estado.tiempo };
        Estado.dosisRestantes = Math.max(0, CONFIG.DOSIS_MAX - Estado.fallos);
        break;
      }
    }
  }

  function procesarFallo() {
    const i = Estado.ultimoIntento;
    const r = Estado.ronda;
    const rachaPerdida = Estado.racha > 0;
    Estado.racha = 0;
    Estado.intentos++;
    Estado.fallos++;
    Estado.partidaPerfecta = false;
    perderCorazon();
    Estado.puntuacion = Math.max(0, Estado.puntuacion - CONFIG.PENALIZACION_FALLO);
    Estado.formulaReforzar = r.formula.id;
    Estado.formulasRepasar.add(r.formula.texto);
    vibrar(200);
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      Estado.shakeCanvasUntil = Estado.tiempo + CONFIG.T_SHAKE;
    }
    UI.marcarEntrada('incorrecto');
    UI.actualizarHUD();
    if (rachaPerdida) UI.shakeHUD('Racha perdida');
    UI.setNarrativa(Narrativa.textoFalloNivel(Estado.nivelActual, Estado.dosisRestantes));
    UI.mostrarPista();
    const lineas = [i.tipoError || 'Intento fallido'];
    if (!i.choqueObstaculo) {
      lineas.push(`Tu respuesta: ${i.respuesta.toFixed(2)} ${i.unidad}`);
      lineas.push(`Correcta: ${i.correcta.toFixed(2)} ${i.unidad}`);
    }
    UI.feedback({ tipo: 'error', titulo: '✕ FALLO', lineas });

    if (Estado.fallos >= CONFIG.DOSIS_MAX) {
      programar(CONFIG.T_FEEDBACK_FALLO, () => UI.mostrarPantallaFinal(false));
      return;
    }

    programar(CONFIG.T_FEEDBACK_FALLO, () => {
      repetirNivel();
    });
  }

  function usarPista() {
    if (!Estado.enJuego || Estado.fase !== 'resultado' || Estado.pistaUsadaEnIntento) return;
    Estado.pistaUsadaEnIntento = true;
    Estado.puntuacion = Math.max(0, Estado.puntuacion - CONFIG.PENALIZACION_PISTA);
    UI.mostrarResolucion(Estado.ultimosPasos || [], true);
    UI.actualizarHUD();
    $('btn-pista').disabled = true;
  }

  function previewTrayectoria() {
    if (!Estado.enJuego || Estado.fase !== 'calculo' || Estado.nivelActual !== 4) return;
    const input = $('entrada-respuesta');
    const valor = parseFloat(String(input.value).replace(',', '.'));
    if (!isFinite(valor) || !Estado.ronda) return;
    const ratio = clamp(valor / Estado.ronda.correcta, 0.05, 4);
    const { v0 } = Fisica.calcularVelocidadOptima();
    Estado.trayectoriaPreview = Fisica.simularTrayectoria(v0 * ratio);
  }

  function animar() {
    if (Estado.particulas.length) {
      const now = Estado.tiempo;
      Estado.particulas = Estado.particulas.filter((p) => (now - p.t0) / 1000 < p.life);
      Estado.particulas.forEach((p) => {
        p.x += p.vx * 0.016;
        p.y += p.vy * 0.016;
        p.vy += 120 * 0.016;
        p.alpha = 1 - (now - p.t0) / 1000 / p.life;
      });
    }
  }

  function reiniciar() {
    iniciar();
  }

  return {
    iniciar,
    verificarYLanzar,
    alAterrizar,
    animar,
    reiniciar,
    usarPista,
    previewTrayectoria,
  };
})();
