// =========================================================================
// PHYSICS ENGINE — Módulo puro (sin DOM, sin canvas)
// =========================================================================
// Todas las funciones trabajan en píxeles y segundos.
// Convención de ángulo: 0° = derecha, 90° = arriba, 180° = izquierda.
// En el juego, el slider va de 10° a 170° y el cañón apunta "hacia arriba".
// Para mantener compatibilidad con el script.js actual (angle - 90),
// aquí usamos la misma convención interna: recibimos "angleDeg" tal cual
// lo maneja el juego y hacemos (angleDeg - 90) al proyectar.
// =========================================================================

(() => {
  "use strict";

  // --- CONSTANTES ---
  const DEFAULTS = Object.freeze({
    GRAVITY: 320,              // px/s²
    BULLET_SPEED_MULT: 7.5,    // v0 = power * mult
    PIXELS_PER_METER: 100,     // solo para mostrar en panel (no afecta física)
    POWER_MIN: 15,
    POWER_MAX: 100,
    ANGLE_MIN: 10,
    ANGLE_MAX: 170
  });

  // --- HELPERS ---
  const toRad = (deg) => (deg * Math.PI) / 180;

  /**
   * Velocidad inicial escalar a partir de la potencia.
   * @param {number} power  Potencia en % (15–100)
   * @param {number} [mult=7.5]  Multiplicador
   * @returns {number} v0 en px/s
   */
  function computeInitialVelocity(power, mult = DEFAULTS.BULLET_SPEED_MULT) {
    return power * mult;
  }

  /**
   * Componentes del vector velocidad inicial.
   * Usa la MISMA convención que script.js: ángulo 90 = arriba.
   *   vx = sin(angle-90) * v0
   *   vy = -cos(angle-90) * v0   (negativo = hacia arriba en canvas)
   * @param {number} angleDeg  Ángulo del slider (10–170)
   * @param {number} v0        Velocidad inicial escalar
   * @returns {{vx:number, vy:number, angleRad:number}}
   */
  function computeComponents(angleDeg, v0) {
    const angleRad = toRad(angleDeg - 90);
    const vx = Math.sin(angleRad) * v0;
    const vy = -Math.cos(angleRad) * v0;
    return { vx, vy, angleRad };
  }

  /**
   * Tiempo total de vuelo hasta volver a la altura de disparo (y = y0).
   * t = 2 * |vy| / g  cuando vy es negativo (sube) y vuelve al mismo y0.
   * Si se lanza hacia abajo (vy > 0), devuelve 0.
   * @param {number} vy  Componente vertical (px/s). Negativo = sube.
   * @param {number} g   Gravedad (px/s²)
   * @returns {number} segundos
   */
  function computeTimeOfFlight(vy, g = DEFAULTS.GRAVITY) {
    if (vy >= 0) return 0;
    return (2 * Math.abs(vy)) / g;
  }

  /**
   * Alcance horizontal teórico hasta volver a y0.
   * @param {number} vx
   * @param {number} t   Tiempo de vuelo
   * @returns {number} px
   */
  function computeRange(vx, t) {
    return vx * t;
  }

  /**
   * Altura máxima sobre el punto de disparo.
   * h_max = vy² / (2g)   (usamos |vy|)
   * @param {number} vy
   * @param {number} g
   * @returns {number} px
   */
  function computeMaxHeight(vy, g = DEFAULTS.GRAVITY) {
    return (vy * vy) / (2 * g);
  }

  /**
   * Genera la trayectoria teórica punto a punto.
   * @param {object} opts
   * @param {number} opts.x0       Posición inicial X (px)
   * @param {number} opts.y0       Posición inicial Y (px, canvas: +abajo)
   * @param {number} opts.vx
   * @param {number} opts.vy
   * @param {number} [opts.g]      Gravedad
   * @param {number} [opts.steps]  Nº de puntos
   * @param {number} [opts.duration]  Duración total (si se omite, se calcula con t_vuelo)
   * @param {number} [opts.yFloor]  Cortar la curva al llegar a este Y (ej: canvas.height)
   * @returns {Array<{x:number,y:number,t:number}>}
   */
  function computeTrajectory(opts) {
    const {
      x0, y0, vx, vy,
      g = DEFAULTS.GRAVITY,
      steps = 60,
      duration,
      yFloor
    } = opts;

    let T = duration;
    if (!T || T <= 0) {
      // Si vy < 0 => sube y vuelve a y0
      // Si vy >= 0 => cae directo; calculamos tiempo hasta yFloor si lo hay
      if (vy < 0) {
        T = computeTimeOfFlight(vy, g);
      } else if (typeof yFloor === "number" && yFloor > y0) {
        // y = y0 + vy*t + 0.5*g*t²  ==> resolver t
        const a = 0.5 * g;
        const b = vy;
        const c = y0 - yFloor;
        const disc = b * b - 4 * a * c;
        T = disc >= 0 ? (-b + Math.sqrt(disc)) / (2 * a) : 1;
      } else {
        T = 1;
      }
    }

    const pts = [];
    for (let i = 0; i <= steps; i++) {
      const t = (T * i) / steps;
      const x = x0 + vx * t;
      const y = y0 + vy * t + 0.5 * g * t * t;
      pts.push({ x, y, t });
      if (typeof yFloor === "number" && y > yFloor) break;
    }
    return pts;
  }

  /**
   * Estado completo de un disparo teórico a partir de parámetros del juego.
   * Reúne todo lo que el panel pedagógico necesita mostrar.
   * @param {object} params
   * @param {number} params.angleDeg
   * @param {number} params.power
   * @param {number} [params.gravity]
   * @param {number} [params.bulletSpeedMult]
   * @param {number} [params.pivotX]
   * @param {number} [params.pivotY]
   * @param {number} [params.cannonLength]
   * @param {number} [params.yFloor]
   * @param {number} [params.steps]
   * @returns {{
   *   angleDeg:number, power:number, gravity:number,
   *   v0:number, vx:number, vy:number,
   *   timeOfFlight:number, range:number, maxHeight:number,
   *   startX:number, startY:number,
   *   trajectory:Array<{x:number,y:number,t:number}>
   * }}
   */
  function computeShot(params) {
    const {
      angleDeg,
      power,
      gravity = DEFAULTS.GRAVITY,
      bulletSpeedMult = DEFAULTS.BULLET_SPEED_MULT,
      pivotX = 0,
      pivotY = 0,
      cannonLength = 0,
      yFloor,
      steps = 60
    } = params;

    const v0 = computeInitialVelocity(power, bulletSpeedMult);
    const { vx, vy, angleRad } = computeComponents(angleDeg, v0);

    const startX = pivotX + Math.sin(angleRad) * cannonLength;
    const startY = pivotY - Math.cos(angleRad) * cannonLength;

    const timeOfFlight = computeTimeOfFlight(vy, gravity);
    const range = computeRange(vx, timeOfFlight);
    const maxHeight = computeMaxHeight(vy, gravity);

    const trajectory = computeTrajectory({
      x0: startX, y0: startY, vx, vy, g: gravity, steps, yFloor
    });

    return {
      angleDeg, power, gravity,
      v0, vx, vy,
      timeOfFlight, range, maxHeight,
      startX, startY,
      trajectory
    };
  }

  // --- EXPORTACIÓN ---
  const PhysicsEngine = {
    DEFAULTS,
    toRad,
    computeInitialVelocity,
    computeComponents,
    computeTimeOfFlight,
    computeRange,
    computeMaxHeight,
    computeTrajectory,
    computeShot
  };

  // Exponer global (para usar sin bundler)
  window.PhysicsEngine = PhysicsEngine;

  // También export ES module si se usa <script type="module">
  // (no rompe nada si el navegador lo ignora)
  if (typeof module !== "undefined" && module.exports) {
    module.exports = PhysicsEngine;
  }
})();