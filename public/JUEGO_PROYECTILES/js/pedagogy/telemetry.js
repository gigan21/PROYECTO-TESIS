// =========================================================================
// TELEMETRY — Módulo puro (sin DOM, sin canvas, sin variables del juego)
// =========================================================================
// Registra qué hace el jugador durante una sesión (disparos, tiempos por
// nivel, cambios de gravedad, reinicios) y, al terminar, devuelve un objeto
// con métricas calculadas + un análisis pedagógico generado por reglas
// condicionales (sin IA).
//
// Convención de ángulo (igual que el juego): el slider va de 10° a 170° y
// 90° es vertical. Por tanto la elevación real sobre la horizontal es
// (90 - |ángulo - 90|). Los ángulos < 50° o > 130° son disparos rasantes.
//
// API pública (window.Telemetry):
//   startSession(), recordShot({angle, power, hit}),
//   recordLevelStart(level), recordLevelEnd(level),
//   recordGravityChange(gravity), recordRestart(),
//   endSession({result, score, levelReached}), getSnapshot(), reset(),
//   generateAnalysis(data)
// =========================================================================

(() => {
  "use strict";

  // --- CONSTANTES (copia local: este módulo no depende de otros) ---
  const STANDARD_GRAVITY = 320;     // px/s², gravedad estándar del juego
  const BULLET_SPEED_MULT = 7.5;    // v0 = power * mult
  const GRAVITY_DEBOUNCE_MS = 600;  // un "ajuste" = una ráfaga de movimientos del slider

  // --- ESTADO INTERNO ---
  let session = null;               // sesión activa (o null)
  let carriedRestarts = 0;          // reinicios acumulados entre sesiones consecutivas
  let lastGravity = STANDARD_GRAVITY; // el slider conserva su valor entre partidas

  // --- HELPERS ---
  const now = () => Date.now();
  const round1 = (n) => Math.round(n * 10) / 10;
  const isNum = (n) => typeof n === "number" && isFinite(n);

  function makeId(ts) {
    return "ses_" + ts.toString(36) + Math.random().toString(36).slice(2, 7);
  }

  /** Alcance teórico en suelo plano: R = v0² · sin(2θ) / g  (px). */
  function theoreticalRange(angleDeg, power, g) {
    const elevation = (90 - Math.abs(angleDeg - 90)) * Math.PI / 180;
    const v0 = power * BULLET_SPEED_MULT;
    return (v0 * v0 * Math.sin(2 * elevation)) / g;
  }

  // --- API ---
  function startSession() {
    const ts = now();
    session = {
      sessionId: makeId(ts),
      startedAt: ts,
      shots: [],
      levels: [],          // niveles cerrados: { level, timeMs }
      openLevel: null,     // nivel en curso: { level, start }
      gravityChanges: 0,
      lastGravityAt: 0,
      restarts: carriedRestarts
    };
  }

  function recordShot(shot) {
    if (!session || !shot) return;
    if (!isNum(shot.angle) || !isNum(shot.power)) return;
    session.shots.push({
      angle: shot.angle,
      power: shot.power,
      hit: !!shot.hit
    });
  }

  function recordLevelStart(level) {
    if (!session) return;
    session.openLevel = { level, start: now() };
  }

  function recordLevelEnd(level) {
    if (!session || !session.openLevel) return;
    if (session.openLevel.level !== level) return;
    session.levels.push({ level, timeMs: now() - session.openLevel.start });
    session.openLevel = null;
  }

  function recordGravityChange(gravity) {
    if (isNum(gravity)) lastGravity = gravity;
    if (!session) return;
    const t = now();
    // El evento "input" del slider se dispara en cada paso al arrastrar:
    // agrupamos ráfagas cercanas para contarlas como un solo ajuste.
    if (t - session.lastGravityAt > GRAVITY_DEBOUNCE_MS) session.gravityChanges++;
    session.lastGravityAt = t;
  }

  function recordRestart() {
    carriedRestarts++;
    if (session) session.restarts = carriedRestarts;
  }

  /** Calcula las métricas a partir de la sesión (sin modificarla). */
  function buildReport(endTs, extra) {
    const s = session;
    const levels = s.levels.map((l) => ({ level: l.level, timeMs: l.timeMs }));
    // Si queda un nivel abierto (p. ej. game over), se cuenta su tiempo parcial.
    if (s.openLevel) {
      levels.push({ level: s.openLevel.level, timeMs: endTs - s.openLevel.start });
    }

    const totalShots = s.shots.length;
    const totalHits = s.shots.filter((x) => x.hit).length;
    const totalMisses = totalShots - totalHits;
    const sumAngle = s.shots.reduce((a, x) => a + x.angle, 0);
    const sumPower = s.shots.reduce((a, x) => a + x.power, 0);
    const totalTimeMs = endTs - s.startedAt;

    return {
      sessionId: s.sessionId,
      startedAt: new Date(s.startedAt).toISOString(),
      endedAt: new Date(endTs).toISOString(),
      totalTimeMs,
      totalTimeSec: round1(totalTimeMs / 1000),
      levels,
      totalShots,
      totalHits,
      totalMisses,
      accuracy: totalShots ? round1((totalHits / totalShots) * 100) : 0,
      avgAngle: totalShots ? round1(sumAngle / totalShots) : 0,
      avgPower: totalShots ? round1(sumPower / totalShots) : 0,
      gravityChanges: s.gravityChanges,
      finalGravity: lastGravity,
      restarts: s.restarts,
      result: extra.result,
      score: extra.score,
      levelReached: extra.levelReached
    };
  }

  function endSession(info) {
    if (!session) return null;
    const { result = "lose", score = 0, levelReached = 1 } = info || {};
    const report = buildReport(now(), {
      result: result === "win" ? "win" : "lose",
      score,
      levelReached
    });
    if (report.result === "win") carriedRestarts = 0; // racha de reintentos terminada
    session = null;
    return report;
  }

  function getSnapshot() {
    if (!session) return null;
    return buildReport(now(), { result: null, score: null, levelReached: null });
  }

  function getSessionId() {
    return session?.sessionId ?? null;
  }

  function reset() {
    session = null;
    carriedRestarts = 0;
    lastGravity = STANDARD_GRAVITY;
  }

  // --- ANÁLISIS PEDAGÓGICO (reglas condicionales, en español) ---
  /**
   * @param {object} data  Objeto devuelto por endSession()
   * @returns {string[]}   2-3 párrafos breves
   */
  function generateAnalysis(data) {
    if (!data) return [];
    const out = [];

    // 1) Precisión (+ consejo de ángulo)
    let p1;
    if (!data.totalShots) {
      p1 = "No se registraron disparos en esta partida, así que todavía no hay precisión que analizar.";
    } else {
      const base = `Tu precisión fue de ${data.accuracy}% (${data.totalHits} ${data.totalHits === 1 ? "acierto" : "aciertos"} de ${data.totalShots} ${data.totalShots === 1 ? "disparo" : "disparos"})`;
      if (data.accuracy >= 70) {
        p1 = `${base}: ¡excelente puntería, estás leyendo muy bien las parábolas!`;
      } else if (data.accuracy >= 40) {
        p1 = `${base}: vas por buen camino. Observa dónde cae cada estela y corrige el ángulo o la fuerza poco a poco.`;
      } else {
        p1 = `${base}: conviene practicar más. Apóyate en la trayectoria predicha y en los ticks de tiempo antes de disparar.`;
      }
      if (data.avgAngle > 130 || data.avgAngle < 50) {
        p1 += ` Además, tu ángulo promedio (${data.avgAngle}°) fue muy rasante: los tiros casi horizontales alcanzan poca altura. Prueba ángulos más cercanos a 90° para llegar a los aliens que vienen desde arriba.`;
      }
    }
    out.push(p1);

    // 2) Gravedad: efecto de los cambios + comparación con la estándar
    const g = data.finalGravity;
    const isStd = Math.abs(g - STANDARD_GRAVITY) < 1;
    const parts = [];

    if (data.gravityChanges > 0) {
      const veces = data.gravityChanges === 1 ? "1 vez" : `${data.gravityChanges} veces`;
      if (g < STANDARD_GRAVITY) {
        parts.push(`Ajustaste la gravedad ${veces}: con menos gravedad el proyectil tarda más en caer, sube más alto y llega más lejos.`);
      } else if (g > STANDARD_GRAVITY) {
        parts.push(`Ajustaste la gravedad ${veces}: con más gravedad el proyectil cae antes, sube menos y su alcance se acorta.`);
      } else {
        parts.push(`Ajustaste la gravedad ${veces} y terminaste en el valor estándar; experimentar así ayuda a ver cómo cambia la parábola.`);
      }
    }

    if (data.totalShots) {
      const rStd = theoreticalRange(data.avgAngle, data.avgPower, STANDARD_GRAVITY);
      const rFin = theoreticalRange(data.avgAngle, data.avgPower, g);
      if (isStd) {
        parts.push(`Con la gravedad estándar (${STANDARD_GRAVITY} px/s²), tu disparo promedio tiene un alcance teórico de unos ${Math.round(rStd)} px.`
          + (data.gravityChanges === 0 ? " Prueba el slider de gravedad para ver cómo cambia la trayectoria." : ""));
      } else {
        const diff = Math.round(Math.abs(rFin / rStd - 1) * 100);
        const sentido = rFin > rStd ? "mayor" : "menor";
        parts.push(`Con g = ${Math.round(g)} px/s², el alcance teórico de tu disparo promedio es de unos ${Math.round(rFin)} px, frente a ${Math.round(rStd)} px con la gravedad estándar (${STANDARD_GRAVITY} px/s²): un ${diff}% ${sentido}, porque el alcance es inversamente proporcional a g.`);
      }
    } else if (!isStd && data.gravityChanges === 0) {
      parts.push(`La gravedad estaba en ${Math.round(g)} px/s² (la estándar es ${STANDARD_GRAVITY} px/s²).`);
    }
    if (parts.length) out.push(parts.join(" "));

    // 3) Cierre motivador según resultado
    if (data.result === "win") {
      out.push("¡Misión cumplida! Cada disparo fue una lección de movimiento parabólico. Ahora intenta superar tu récord con menos fallos.");
    } else {
      out.push("No te rindas: cada intento te enseña más sobre ángulo, fuerza y gravedad. ¡Reintenta y llega más lejos!");
    }

    return out;
  }

  // --- EXPORTACIÓN ---
  const Telemetry = {
    startSession,
    recordShot,
    recordLevelStart,
    recordLevelEnd,
    recordGravityChange,
    recordRestart,
    endSession,
    getSnapshot,
    getSessionId,
    reset,
    generateAnalysis
  };

  window.Telemetry = Telemetry;

  // Export CommonJS para pruebas en Node (no afecta al navegador)
  if (typeof module !== "undefined" && module.exports) {
    module.exports = Telemetry;
  }
})();
