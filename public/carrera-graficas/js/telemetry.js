/* telemetry.js — Sesión Carrera de Gráficas (sin DOM). */
(() => {
  'use strict';

  let session = null;

  const now = () => Date.now();

  function makeId(ts) {
    return `cr_${ts.toString(36)}${Math.random().toString(36).slice(2, 7)}`;
  }

  function startSession() {
    const ts = now();
    session = {
      sessionId: makeId(ts),
      startedAt: ts,
      events: [],
    };
  }

  function recordRound(payload) {
    if (!session) return;
    session.events.push({
      round_number: payload.round_number,
      question_type: payload.question_type,
      graph_kind: payload.graph_kind,
      graph_type: payload.graph_type,
      topic: payload.topic,
      correct: !!payload.correct,
      answered_in_seconds: payload.answered_in_seconds ?? 0,
      timed_out: !!payload.timed_out,
      wrong_answer: payload.wrong_answer ?? null,
    });
  }

  function accuracyFor(events, keyFn) {
    const buckets = {};
    events.forEach((e) => {
      const key = keyFn(e);
      if (!key) return;
      if (!buckets[key]) buckets[key] = { ok: 0, total: 0 };
      buckets[key].total += 1;
      if (e.correct) buckets[key].ok += 1;
    });
    const out = {};
    Object.keys(buckets).forEach((k) => {
      const b = buckets[k];
      out[k] = b.total > 0 ? Math.round((b.ok / b.total) * 1000) / 10 : 0;
    });
    return out;
  }

  function endSession(flags) {
    if (!session) return null;

    const endTs = now();
    const events = session.events.slice();
    const hits = events.filter((e) => e.correct).length;
    const misses = events.length - hits;
    const accuracy = events.length > 0 ? Math.round((hits / events.length) * 1000) / 10 : 0;

    // 🔗 FIX WEAK_AREAS — weak: todos los fallados; strong: solo los 100% acertados
const weakSet = new Set();
const strongSet = new Set();
events.forEach((e) => {
  if (!e.topic) return;
  if (e.correct) strongSet.add(e.topic);
  else weakSet.add(e.topic);
});
const weakAreas = [...weakSet];
const strongAreas = [...strongSet].filter((t) => !weakSet.has(t));

    const exploded = !!flags.exploded;
    const completed = !!flags.completed;
    const won = !!flags.won;

    return {
      sessionId: session.sessionId,
      hits,
      misses,
      accuracy,
      exploded,
      completed,
      won,
      totalTimeSec: Math.max(0, Math.round((endTs - session.startedAt) / 1000)),
      weakAreas,
      strongAreas,
      byTypeAccuracy: accuracyFor(events, (e) => e.question_type),
      byGraphKindAccuracy: accuracyFor(events, (e) => e.graph_kind),
      events,
    };
  }

  window.ChartRaceTelemetry = {
    startSession,
    recordRound,
    endSession,
  };
})();
