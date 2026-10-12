// =========================================================================
// PEDAGOGY OVERLAY — Dibuja vectores, trayectoria y ticks de tiempo
// =========================================================================
// Módulo visual PURO. No conoce el juego. Solo recibe datos y dibuja.
// Depende de: physics-engine.js (window.PhysicsEngine)
// =========================================================================

(() => {
  "use strict";

  // --- ESTILOS (colores y grosores) ---
  const STYLE = Object.freeze({
    trajectory:   { color: "#ff2bd6", width: 2, dash: [6, 6] },
    v0Vector:     { color: "#7a4dff", width: 3, headSize: 10 },
    vxVector:     { color: "#19f2ff", width: 3, headSize: 9 },
    vyVector:     { color: "#ffd166", width: 3, headSize: 9 },
    tickDot:      { color: "#ffffff", radius: 2, alpha: 0.6 },
    tickLabel:    { color: "#eaf8ff", font: "11px Orbitron, monospace", offsetY: -6 },
    apex:         { color: "#ffd166", radius: 5 },
    landing:      { color: "#ff2bd6", radius: 5 }
  });

  // Escalas de dibujo (px de pantalla por px/s de velocidad)
  const VECTOR_SCALE = 0.15;

  // --- HELPERS ---
  function arrowHead(ctx, x, y, angle, color, size) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(angle);
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(-size, -size / 2);
    ctx.lineTo(-size, size / 2);
    ctx.closePath();
    ctx.fill();
    ctx.restore();
  }

  function drawArrow(ctx, x0, y0, x1, y1, color, width, headSize) {
    const angle = Math.atan2(y1 - y0, x1 - x0);
    ctx.strokeStyle = color;
    ctx.lineWidth = width;
    ctx.lineCap = "round";
    ctx.beginPath();
    ctx.moveTo(x0, y0);
    ctx.lineTo(x1, y1);
    ctx.stroke();
    arrowHead(ctx, x1, y1, angle, color, headSize);
  }

  // =====================================================================
  // DIBUJOS PÚBLICOS
  // =====================================================================

  /**
   * Dibuja la trayectoria teórica punteada.
   * @param {CanvasRenderingContext2D} ctx
   * @param {Array<{x:number,y:number,t:number}>} points
   * @param {object} [opts]  {color, width, dash, showApex, showLanding}
   */
  function drawPredictedTrajectory(ctx, points, opts = {}) {
    if (!points || points.length < 2) return;

    const color = opts.color || STYLE.trajectory.color;
    const width = opts.width || STYLE.trajectory.width;
    const dash  = opts.dash  || STYLE.trajectory.dash;

    ctx.save();
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (let i = 1; i < points.length; i++) {
      ctx.lineTo(points[i].x, points[i].y);
    }
    ctx.strokeStyle = color;
    ctx.lineWidth = width;
    ctx.setLineDash(dash);
    ctx.stroke();
    ctx.restore();

    // Marcar ápice (punto más alto = menor Y)
    if (opts.showApex !== false) {
      let apex = points[0];
      for (const p of points) if (p.y < apex.y) apex = p;
      ctx.beginPath();
      ctx.arc(apex.x, apex.y, STYLE.apex.radius, 0, Math.PI * 2);
      ctx.fillStyle = STYLE.apex.color;
      ctx.fill();
    }

    // Marcar aterrizaje (último punto)
    if (opts.showLanding !== false) {
      const last = points[points.length - 1];
      ctx.beginPath();
      ctx.arc(last.x, last.y, STYLE.landing.radius, 0, Math.PI * 2);
      ctx.fillStyle = STYLE.landing.color;
      ctx.fill();
    }
  }

  /**
   * Dibuja el vector v₀ desde el pivote del cañón.
   * @param {CanvasRenderingContext2D} ctx
   * @param {number} pivotX
   * @param {number} pivotY
   * @param {number} vx   Componente X
   * @param {number} vy   Componente Y (canvas: +abajo)
   * @param {object} [opts]  {scale, color, width, label}
   */
  function drawV0Vector(ctx, pivotX, pivotY, vx, vy, opts = {}) {
    const scale = opts.scale ?? VECTOR_SCALE;
    const color = opts.color || STYLE.v0Vector.color;
    const width = opts.width || STYLE.v0Vector.width;

    const x1 = pivotX + vx * scale;
    const y1 = pivotY + vy * scale;
    drawArrow(ctx, pivotX, pivotY, x1, y1, color, width, STYLE.v0Vector.headSize);

    if (opts.label) {
      ctx.save();
      ctx.fillStyle = color;
      ctx.font = "bold 12px Orbitron, monospace";
      ctx.fillText(opts.label, x1 + 8, y1 - 4);
      ctx.restore();
    }
  }

  /**
   * Dibuja los componentes vx (horizontal) y vy (vertical) sobre un punto (bala).
   */
  function drawComponentVectors(ctx, x, y, vx, vy, opts = {}) {
    const scale = opts.scale ?? VECTOR_SCALE;
    const showLabels = opts.labels !== false;

    // vx — flecha horizontal
    if (Math.abs(vx) > 0.5) {
      const x1 = x + vx * scale;
      drawArrow(ctx, x, y, x1, y, STYLE.vxVector.color, STYLE.vxVector.width, STYLE.vxVector.headSize);
      if (showLabels) {
        ctx.save();
        ctx.fillStyle = STYLE.vxVector.color;
        ctx.font = "10px Orbitron, monospace";
        ctx.fillText("vx", x1 + 4, y + 3);
        ctx.restore();
      }
    }

    // vy — flecha vertical
    if (Math.abs(vy) > 0.5) {
      const y1 = y + vy * scale;
      drawArrow(ctx, x, y, x, y1, STYLE.vyVector.color, STYLE.vyVector.width, STYLE.vyVector.headSize);
      if (showLabels) {
        ctx.save();
        ctx.fillStyle = STYLE.vyVector.color;
        ctx.font = "10px Orbitron, monospace";
        ctx.fillText("vy", x + 4, y1 - 4);
        ctx.restore();
      }
    }
  }

  /**
   * Dibuja marcadores de tiempo cada `everySec` sobre la trayectoria.
   * @param {Array<{x,y,t}>} points
   * @param {number} everySec  Ej: 0.25
   */
  function drawTimeTicks(ctx, points, everySec = 0.25) {
    if (!points || points.length < 2) return;

    let nextT = everySec;
    ctx.save();
    for (const p of points) {
      if (p.t >= nextT) {
        ctx.beginPath();
        ctx.arc(p.x, p.y, STYLE.tickDot.radius, 0, Math.PI * 2);
        ctx.fillStyle = STYLE.tickDot.color;
        ctx.globalAlpha = STYLE.tickDot.alpha;
        ctx.fill();

        // Etiqueta de tiempo
        ctx.globalAlpha = 0.85;
        ctx.fillStyle = STYLE.tickLabel.color;
        ctx.font = STYLE.tickLabel.font;
        ctx.fillText(nextT.toFixed(2) + "s", p.x + 5, p.y + STYLE.tickLabel.offsetY);

        nextT += everySec;
      }
    }
    ctx.restore();
  }

  /**
   * Dibuja la estela de una bala real con ticks de tiempo.
   * @param {Array<{x,y}>} trail
   * @param {object} [opts]  {color, width, dotEvery, tickEvery}
   */
  function drawTrailWithTicks(ctx, trail, opts = {}) {
    if (!trail || trail.length < 2) return;
    const color = opts.color || "rgba(25,242,255,0.55)";
    const width = opts.width || 2;

    ctx.save();
    ctx.beginPath();
    ctx.moveTo(trail[0].x, trail[0].y);
    for (let i = 1; i < trail.length; i++) ctx.lineTo(trail[i].x, trail[i].y);
    ctx.strokeStyle = color;
    ctx.lineWidth = width;
    ctx.stroke();
    ctx.restore();
  }

  /**
   * Dibuja un panel pequeño con datos de la bala en vuelo (x, y, vx, vy, t).
   * @param {object} data  {x, y, vx, vy, t}
   * @param {object} [opts] {x, y, bg, color, font}
   */
  function drawLiveDataBox(ctx, data, opts = {}) {
    const bx = opts.x ?? 20;
    const by = opts.y ?? 120;
    const bg = opts.bg ?? "rgba(13,10,36,0.85)";
    const fg = opts.color ?? "#eaf8ff";
    const font = opts.font ?? "12px Orbitron, monospace";
    const lines = [
      `t  = ${data.t.toFixed(2)} s`,
      `x  = ${data.x.toFixed(0)} px`,
      `y  = ${data.y.toFixed(0)} px`,
      `vx = ${data.vx.toFixed(1)} px/s`,
      `vy = ${data.vy.toFixed(1)} px/s`
    ];

    ctx.save();
    ctx.font = font;
    const padding = 10;
    const lineH = 16;
    const w = 150;
    const h = padding * 2 + lineH * lines.length;

    ctx.fillStyle = bg;
    ctx.strokeStyle = "#1e1a3a";
    ctx.lineWidth = 1;
    roundRect(ctx, bx, by, w, h, 8);
    ctx.fill();
    ctx.stroke();

    ctx.fillStyle = fg;
    lines.forEach((ln, i) => {
      ctx.fillText(ln, bx + padding, by + padding + lineH * (i + 1) - 4);
    });
    ctx.restore();
  }

  function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y,     x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x,     y + h, r);
    ctx.arcTo(x,     y + h, x,     y,     r);
    ctx.arcTo(x,     y,     x + w, y,     r);
    ctx.closePath();
  }

  // --- EXPORTACIÓN ---
  const PedagogyOverlay = {
    STYLE,
    VECTOR_SCALE,
    drawPredictedTrajectory,
    drawV0Vector,
    drawComponentVectors,
    drawTimeTicks,
    drawTrailWithTicks,
    drawLiveDataBox
  };

  window.PedagogyOverlay = PedagogyOverlay;
})();