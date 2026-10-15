/* telemetry.js — Sesión de telemetría (sin DOM). */
(() => {
  'use strict';

  const ERROR_MAP = {
    'Error de concepto': 'concepto',
    'Error de cálculo': 'calculo',
    'Casi. Revisa los decimales': 'decimales',
  };

  let session = null;

  const now = () => Date.now();

  function makeId(ts) {
    return `cure_${ts.toString(36)}${Math.random().toString(36).slice(2, 7)}`;
  }

  function startSession() {
    const ts = now();
    session = {
      sessionId: makeId(ts),
      startedAt: ts,
      answers: [],
      levels: [],
      openLevel: null,
      pistaUsadas: 0,
      hits: 0,
      misses: 0,
    };
  }

  function recordAnswer({ formula, correct, errorType }) {
    if (!session) return;
    session.answers.push({ formula: formula || '', correct: !!correct, errorType: errorType || null });
    if (correct) session.hits += 1;
    else session.misses += 1;
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

  function recordPista() {
    if (!session) return;
    session.pistaUsadas += 1;
  }

  function buildPayload(endTs, extra = {}) {
    if (!session) return null;

    const attempts = session.hits + session.misses;
    const accuracy = attempts > 0 ? Math.round((session.hits / attempts) * 1000) / 10 : 0;

    const errorsByType = { concepto: 0, calculo: 0, decimales: 0 };
    session.answers.forEach((a) => {
      if (a.correct) return;
      const key = ERROR_MAP[a.errorType];
      if (key) errorsByType[key] += 1;
    });

    const levels = session.levels.map((l) => ({ level: l.level, timeMs: l.timeMs }));
    if (session.openLevel) {
      levels.push({
        level: session.openLevel.level,
        timeMs: endTs - session.openLevel.start,
      });
    }

    const score = typeof extra.score === 'number' ? extra.score : 0;
    const rankFn = typeof rangoPorPuntos === 'function' ? rangoPorPuntos : null;
    const rank = rankFn ? rankFn(score).nombre : '';

    return {
      sessionId: session.sessionId,
      levelReached: extra.levelReached ?? 1,
      nivelesCompletados: extra.nivelesCompletados ?? 0,
      attempts,
      hits: session.hits,
      misses: session.misses,
      accuracy,
      score,
      maxStreak: extra.maxStreak ?? 0,
      won: !!extra.won,
      finished: !!extra.finished,
      abandoned: !!extra.abandoned,
      ...(extra.levelCompleted !== undefined && extra.levelCompleted !== null 
        ? { levelCompleted: extra.levelCompleted } 
        : {}),
      pistaUsadas: session.pistaUsadas,
      totalTimeSec: Math.max(0, Math.round((endTs - session.startedAt) / 1000)),
      formulasMastered: extra.formulasMastered ?? [],
      formulasReview: extra.formulasReview ?? [],
      errorsByType,
      rank,
      levels,
    };
  }

  /**
   * @param {object} extra won, finished, abandoned, levelCompleted, levelReached, ...
   */
  function snapshot(extra = {}) {
    return buildPayload(now(), extra);
  }

  /**
   * @param {object} extra won, levelReached, nivelesCompletados, score, maxStreak, formulasMastered, formulasReview
   */
  function endSession(extra = {}) {
    const payload = buildPayload(now(), { ...extra, finished: extra.finished ?? true });
    session = null;
    return payload;
  }

  function getSessionId() {
    return session?.sessionId ?? null;
  }

  window.CureTelemetry = {
    startSession,
    recordAnswer,
    recordLevelStart,
    recordLevelEnd,
    recordPista,
    snapshot,
    endSession,
    getSessionId,
  };
})();
