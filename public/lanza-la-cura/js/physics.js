/* physics.js — Física hacia Lyra (no suelo) con viento y obstáculos. */
const Fisica = (() => {
  const ESCALA = CONFIG.ESCALA;

  function lyraFloatOffset(t) {
    if (!Estado.lyra.enGlobo) return 0;
    return Math.sin((t / CONFIG.GLOBO_FLOT_PERIOD) * Math.PI * 2) * CONFIG.GLOBO_FLOT_AMP;
  }

  function lyraPosicion(t) {
    const tUse = t ?? Estado.tiempo;
    return {
      x: Estado.lyra.x,
      y: Estado.lyra.y + lyraFloatOffset(tUse),
    };
  }

  function sueloY() {
    return Estado.sueloY ?? CONFIG.SUELO_Y;
  }

  function gRonda() {
    const f = Estado.nivelConfig?.gravedad ?? 1;
    return CONFIG.GRAVEDAD * f;
  }

  function vientoRonda() {
    return Estado.nivelConfig?.viento ?? Estado.ronda?.viento ?? 0;
  }

  function puntoLanzamiento() {
    return {
      x: Estado.medico.x + CONFIG.LANZ_OFFSET_X,
      y: Estado.medico.y + CONFIG.LANZ_OFFSET_Y,
    };
  }

  function calcularVelocidadOptima(opts = {}) {
    const g = opts.g ?? gRonda();
    const aw = opts.viento ?? vientoRonda();
    const o = puntoLanzamiento();
    const target = lyraPosicion();
    const dx = (target.x - o.x) / ESCALA;
    const dy = Math.max(0.05, (o.y - target.y) / ESCALA);
    const t = Math.sqrt(2 * dy / g);
    const v0 = (dx - 0.5 * aw * t * t) / t;
    return { v0, t, dx, dy, g, viento: aw, targetY: target.y };
  }

  function puntoEnTrayectoria(t, vx0Launch, aw, g, x0, y0) {
    const awPx = aw * ESCALA;
    const gPx = g * ESCALA;
    return {
      x: x0 + vx0Launch * t + 0.5 * awPx * t * t,
      y: y0 + 0.5 * gPx * t * t,
    };
  }

  function intersectaObstaculo(px, py, prevX, prevY) {
    for (const obs of Estado.obstaculosInstanciados) {
      if (!obs.bloquea) continue;
      const r = obs.rect;
      if (puntoEnRect(px, py, r) || segmentoRect(prevX, prevY, px, py, r)) return true;
    }
    return false;
  }

  function puntoEnRect(x, y, r) {
    return x >= r.x && x <= r.x + r.w && y >= r.y && y <= r.y + r.h;
  }

  function segmentoRect(x1, y1, x2, y2, r) {
    const steps = 8;
    for (let i = 0; i <= steps; i++) {
      const k = i / steps;
      const x = x1 + (x2 - x1) * k;
      const y = y1 + (y2 - y1) * k;
      if (puntoEnRect(x, y, r)) return true;
    }
    return false;
  }

  function simularTrayectoria(v0, pasos = 40) {
    const o = puntoLanzamiento();
    const g = gRonda();
    const aw = vientoRonda();
    const target = lyraPosicion();
    const dy = Math.max(0.05, (o.y - target.y) / ESCALA);
    const tVuelo = Math.sqrt(2 * dy / g);
    const vx0 = v0 * ESCALA;
    const pts = [];
    for (let i = 0; i <= pasos; i++) {
      const t = (tVuelo * i) / pasos;
      pts.push(puntoEnTrayectoria(t, vx0, aw, g, o.x, o.y));
    }
    return pts;
  }

  function lanzar(velocidadX) {
    const o = puntoLanzamiento();
    const g = gRonda();
    const aw = vientoRonda();
    const target = lyraPosicion();
    const dy = Math.max(0.05, (o.y - target.y) / ESCALA);
    const j = Estado.jeringa;
    j.activa = true;
    j.x0 = o.x;
    j.y0 = o.y;
    j.x = o.x;
    j.y = o.y;
    j.vx0Launch = velocidadX * ESCALA;
    j.vx = j.vx0Launch;
    j.vy = 0;
    j.t = 0;
    j.tVuelo = Math.sqrt(2 * dy / g);
    j.targetY = target.y;
    j.angulo = 0;
    j.aterrizo = false;
    j.exitoso = false;
    j.tAterrizaje = 0;
    j.traza = [];
    j.viento = aw;
    j.g = g;
    j.choqueObstaculo = false;
    j.prevX = o.x;
    j.prevY = o.y;
    Estado.trayectoriaPreview = null;
  }

  function actualizar(dt) {
    const j = Estado.jeringa;
    if (!j.activa || j.aterrizo) return;

    const g = j.g ?? gRonda();
    const aw = j.viento ?? 0;
    const gPx = g * ESCALA;
    const awPx = aw * ESCALA;

    const tNext = Math.min(j.t + dt, j.tVuelo);
    const prevX = j.x;
    const prevY = j.y;
    j.t = tNext;
    j.x = j.x0 + j.vx0Launch * j.t + 0.5 * awPx * j.t * j.t;
    j.y = j.y0 + 0.5 * gPx * j.t * j.t;
    j.vx = j.vx0Launch + awPx * j.t;
    j.vy = gPx * j.t;
    j.angulo = Math.atan2(j.vy, j.vx);
    j.traza.push({ x: j.x, y: j.y });

    if (intersectaObstaculo(j.x, j.y, prevX, prevY)) {
      j.choqueObstaculo = true;
      j.aterrizo = true;
      j.activa = false;
      j.tAterrizaje = Estado.tiempo;
      Game.alAterrizar();
      return;
    }

    if (j.t >= j.tVuelo - 1e-9) {
      j.y = j.targetY;
      j.aterrizo = true;
      j.activa = false;
      j.tAterrizaje = Estado.tiempo;
      Game.alAterrizar();
    }
  }

  function evaluarImpactoLyra() {
    const j = Estado.jeringa;
    const lyra = lyraPosicion(j.tAterrizaje);
    const dist = Math.hypot(j.x - lyra.x, j.y - lyra.y);
    const radio = Estado.ronda?.radioPx ?? CONFIG.RADIO_LYRA_BASE_PX;
    return { dist, radio, aciertoFisico: dist <= radio + 1e-6, lyra };
  }

  function reiniciar() {
    const j = Estado.jeringa;
    j.activa = false;
    j.aterrizo = false;
    j.exitoso = false;
    j.choqueObstaculo = false;
    j.traza = [];
  }

  return {
    lyraFloatOffset,
    lyraPosicion,
    sueloY,
    puntoLanzamiento,
    calcularVelocidadOptima,
    simularTrayectoria,
    lanzar,
    actualizar,
    evaluarImpactoLyra,
    reiniciar,
    gRonda,
    vientoRonda,
  };
})();
