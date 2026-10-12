// =========================================================================
// ARTILLERÍA ORBITAL - LÓGICA COMPLETA CORREGIDA
// =========================================================================

(() => {
  "use strict";

  // --- Parámetros desde Laravel (iframe query string) ---
  const urlParams = new URLSearchParams(window.location.search);
  const REWARD_ENDPOINT = urlParams.get("reward_url") || "/estudiante/proyectiles/api/reward";
  const REWARD_CSRF = urlParams.get("csrf") || "";

  function currentSessionId() {
    if (window.Telemetry && typeof Telemetry.getSessionId === "function") {
      return Telemetry.getSessionId();
    }
    const snap = window.Telemetry ? Telemetry.getSnapshot() : null;
    return snap?.sessionId ?? null;
  }

  // --- CONFIGURACIÓN Y CONSTANTES ---
  const CONFIG = {
    gravity: 320,
    bulletSpeedMult: 7.5,
    shipW: 0, shipH: 0,
    alienW: 35, alienH: 30,
    alienPad: 8,          // Reduce la hitbox del alien respecto al GIF
    bulletR: 5,
    trailLen: 55,
    debugHitbox: true,    // Tecla H para activar/desactivar
    cannonPivot: 0.20,    // Altura del pivote del cañón (fracción de shipH, hacia arriba)
    cannonLength: 54,     // Largo del cañón en píxeles

    // 🔗 TORRETA: sprite del cañón. El PNG original apunta hacia la DERECHA;
    // en render() se gira -90° para que quede apuntando "hacia arriba" (ángulo 90°).
    cannonSpriteW: 105,        // Ancho del sprite en px (largo del cañón: pivote -> boca ≈ cannonLength)
    cannonSpriteH: 70,         // Alto del sprite en px (mantiene la proporción 612x408)
    cannonSpritePivotX: 0.42,  // Fracción del ancho del sprite donde está el eje de giro (a lo largo del cañón)
    cannonSpritePivotY: 0.50,  // Fracción del alto del sprite donde está el eje de giro
    cannonSpriteOffsetY: 0,    // Ajuste fino (px) perpendicular al cañón si la base no coincide con el pivote

    // Hitboxes de la nave: fracciones relativas al centro de la IMAGEN
    // x1/x2 -> fracción de shipW | y1/y2 -> fracción de shipH
    // (negativo = izquierda/arriba, positivo = derecha/abajo)
        // Hitboxes de la nave: fracciones relativas al centro de la IMAGEN
    // x1/x2 -> fracción de shipW | y1/y2 -> fracción de shipH
    // (negativo = izquierda/arriba, positivo = derecha/abajo)
    shipHitboxes: [
      { x1: -0.06, x2: 0.06, y1: -0.30, y2: -0.05 }, // Núcleo del Cañón (Muy estrecho: 12% del ancho total)
      { x1: -0.40, x2: 0.45, y1: -0.05, y2:  0.40 }  // Base central de la nave (60% del ancho)
    ],

    levels: [
      // aliens: Cantidad | speed: Velocidad lateral | descent: Cuánto bajan al tocar el borde
      { aliens: 8, speed: 5, descent: 15 },
      { aliens: 20, speed: 40, descent: 25 },
      { aliens: 32, speed: 80, descent: 30 },
      { aliens: 40, speed: 95, descent: 35 },
      { aliens: 50, speed: 110, descent: 40 }
    ]
  };

  // --- REFERENCIAS AL DOM ---
  const $ = id => document.getElementById(id);
  const canvas = $("game-canvas");
  const ctx = canvas.getContext("2d");
  const assetShelf = $("asset-shelf");

  // HUD
  const levelVal = $("level-value");
  const enemyCount = $("enemy-count");
  const enemyBar = $("enemy-bar");
  const scoreVal = $("score-value");
  const bestVal = $("best-value");
  const pips = document.querySelectorAll(".pips i");

  // Consola
  const angleSlider = $("angle-slider");
  const angleReadout = $("angle-readout");
  const dialNeedle = $("dial-needle");
  const powerSlider = $("power-slider");
  const powerReadout = $("power-readout");
  const powerCells = $("power-cells");
  const fireZone = $("fire-zone");
  const fireBtn = $("fire-btn");

  // Pantallas
  const screenMenu = $("screen-menu");
  const screenPause = $("screen-pause");
  const screenOver = $("screen-over");
  const screenWin = $("screen-win");
  const banner = $("banner");
  const bannerTitle = $("banner-title");
  const bannerSub = $("banner-sub");

  // --- ESTADO DEL JUEGO ---
  let state = "MENU";
  let score = 0;
  let bestScore = parseInt(localStorage.getItem("orbitalBest")) || 0;
  let currentLevel = 0;
  let aliens = [];
  let bullets = [];
  let particles = [];
  let ship = { x: 0, y: 0, angle: 110, power: 60 };
  let lastTime = 0;

  // --- RECURSOS GRÁFICOS ---
  const bgImg = new Image();
  bgImg.src = "fondodeljuego.jpg";

  const shipImg = new Image();
  shipImg.src = "nave.png";
  assetShelf.appendChild(shipImg);

  const alienImg = new Image();
  alienImg.src = "alien.gif";
  assetShelf.appendChild(alienImg);

  // 🔗 TORRETA: sprite del cañón
  const torretImg = new Image();
  torretImg.src = "torretadedisparo.png";
  assetShelf.appendChild(torretImg);

  // --- AJUSTES DEL CANVAS ---
  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    ship.x = canvas.width / 2;
    ship.y = canvas.height - 210; // La subimos un poco para que no tape los controles

    CONFIG.shipW = canvas.width * 0.6;  // Ocupa el 60% del ancho de la pantalla
    CONFIG.shipH = CONFIG.shipW * 0.45;
  }
  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();

  // --- HITBOXES ---
  // Devuelve las hitboxes de la nave en coordenadas de pantalla
  function getShipBoxes() {
    return CONFIG.shipHitboxes.map(h => ({
      left:   ship.x + h.x1 * CONFIG.shipW,
      right:  ship.x + h.x2 * CONFIG.shipW,
      top:    ship.y + h.y1 * CONFIG.shipH,
      bottom: ship.y + h.y2 * CONFIG.shipH
    }));
  }

  function getAlienBox(a) {
    const pad = CONFIG.alienPad;
    return {
      left:   a.x - a.w / 2 + pad,
      right:  a.x + a.w / 2 - pad,
      top:    a.y - a.h / 2 + pad,
      bottom: a.y + a.h / 2 - pad
    };
  }

  function boxesOverlap(a, b) {
    return a.right > b.left && a.left < b.right &&
           a.bottom > b.top && a.top < b.bottom;
  }

  // --- INICIALIZACIÓN DE LA UI ---
  function initUI() {
    bestVal.textContent = bestScore;

    powerCells.innerHTML = "";
    for (let i = 0; i < 20; i++) {
      const s = document.createElement("span");
      powerCells.appendChild(s);
    }
    updatePowerUI();

    angleSlider.addEventListener("input", updateAngleUI);
    powerSlider.addEventListener("input", updatePowerUI);
    updateAngleUI();

    $("start-btn").addEventListener("click", startGame);
    $("resume-btn").addEventListener("click", resumeGame);
    $("retry-btn").addEventListener("click", startGame);
    $("again-btn").addEventListener("click", startGame);
    $("pause-btn").addEventListener("click", pauseGame);
    $("sound-btn").addEventListener("click", (e) => e.currentTarget.classList.toggle("muted"));

    // Botón de disparo (Pulsa y suelta)
    fireBtn.addEventListener('mousedown', () => { if (state === "PLAYING") fireZone.classList.add('charging'); });
    fireBtn.addEventListener('mouseup', () => { fireZone.classList.remove('charging'); shoot(); });
    fireBtn.addEventListener('mouseleave', () => fireZone.classList.remove('charging'));
    fireBtn.addEventListener('touchstart', (e) => { e.preventDefault(); if (state === "PLAYING") fireZone.classList.add('charging'); });
    fireBtn.addEventListener('touchend', (e) => { e.preventDefault(); fireZone.classList.remove('charging'); shoot(); });

    // Teclado
    window.addEventListener("keydown", (e) => {
      if (e.code === "Space" && state === "PLAYING") { e.preventDefault(); shoot(); }
      if (e.code === "KeyP") { state === "PLAYING" ? pauseGame() : (state === "PAUSED" ? resumeGame() : null); }
      if (e.code === "KeyH") { CONFIG.debugHitbox = !CONFIG.debugHitbox; }
      if (e.code === "ArrowLeft" || e.code === "KeyA") { angleSlider.value = Math.max(10, parseInt(angleSlider.value) - 2); updateAngleUI(); }
      if (e.code === "ArrowRight" || e.code === "KeyD") { angleSlider.value = Math.min(170, parseInt(angleSlider.value) + 2); updateAngleUI(); }
      if (e.code === "ArrowUp") { powerSlider.value = Math.min(100, parseInt(powerSlider.value) + 2); updatePowerUI(); }
      if (e.code === "ArrowDown") { powerSlider.value = Math.max(15, parseInt(powerSlider.value) - 2); updatePowerUI(); }
    });
  }

    function updateAngleUI() {
    const val = angleSlider.value;
    angleReadout.textContent = val + "°";
    ship.angle = parseInt(val);
    const rotation = val - 90;
    dialNeedle.setAttribute("transform", `rotate(${rotation} 50 54)`);
    // 🔗 PEDAGOGY
    refreshPedagogyPanel();
  }

    function updatePowerUI() {
    const val = powerSlider.value;
    powerReadout.textContent = val + "%";
    ship.power = parseInt(val);

    const cells = powerCells.querySelectorAll("span");
    const activeCells = Math.ceil(val / 5); // 20 celdas * 5 = 100%
    cells.forEach((c, i) => {
      const isOn = i < activeCells;
      c.classList.toggle("on", isOn);
      if (isOn) {
        const hue = 190 - (i / 20) * 160;
        c.style.setProperty('--h', hue);
      }
    });
    // 🔗 PEDAGOGY
    refreshPedagogyPanel();
  }

  function updateHUD() {
    levelVal.textContent = currentLevel + 1;
    scoreVal.textContent = score;

    pips.forEach((p, i) => {
      p.classList.remove("done", "now");
      if (i < currentLevel) p.classList.add("done");
      if (i === currentLevel) p.classList.add("now");
    });

    const totalAliens = CONFIG.levels[currentLevel]?.aliens || 1;
    const remaining = aliens.length;
    enemyCount.textContent = remaining;
    enemyBar.style.setProperty("--fill", remaining / totalAliens);
  }

  function showScreen(screen) {
    [screenMenu, screenPause, screenOver, screenWin].forEach(s => {
      s.classList.remove("is-open");
      s.inert = true;
    });

    if (screen) {
      screen.classList.add("is-open");
      screen.inert = false;
    }
  }

  function showBanner(title, sub) {
    bannerTitle.textContent = title;
    bannerSub.textContent = sub;
    banner.classList.remove("show");
    void banner.offsetWidth;
    banner.classList.add("show");
  }

  // --- LÓGICA DEL JUEGO ---
  function startGame() {
    // 🔗 TELEMETRY: nueva sesión (si viene de "Reintentar" tras game over, cuenta un reinicio antes)
    if (window.Telemetry) {
      if (state === "OVER") Telemetry.recordRestart();
      Telemetry.startSession();
    }
    score = 0;
    currentLevel = 0;
    aliens = [];
    bullets = [];
    particles = [];
    state = "PLAYING";
    showScreen(null);
    resizeCanvas();
    spawnLevel();
    updateHUD();
    lastTime = performance.now();
  }

  function pauseGame() {
    if (state === "PLAYING") { state = "PAUSED"; showScreen(screenPause); }
  }

  function resumeGame() {
    if (state === "PAUSED") { state = "PLAYING"; showScreen(null); lastTime = performance.now(); }
  }

  function saveBest() {
    if (score > bestScore) {
      bestScore = score;
      localStorage.setItem("orbitalBest", bestScore);
      bestVal.textContent = bestScore;
    }
  }

  // 🔗 TELEMETRY: formato mm:ss
  function formatMMSS(ms) {
    const total = Math.max(0, Math.round(ms / 1000));
    const m = Math.floor(total / 60);
    const sec = total % 60;
    return String(m).padStart(2, "0") + ":" + String(sec).padStart(2, "0");
  }

  // 🔗 TELEMETRY: pinta el informe de sesión + análisis en el contenedor dado
  function renderTelemetrySummary(container, data) {
    if (!container) return;
    container.textContent = "";
    if (!data) return;

    const makeRow = (label, value) => {
      const row = document.createElement("div");
      row.className = "telem-row";
      const l = document.createElement("span");
      l.className = "telem-label";
      l.textContent = label;
      const v = document.createElement("span");
      v.className = "telem-value";
      v.textContent = value;
      row.appendChild(l);
      row.appendChild(v);
      return row;
    };

    // Bloque 1: informe
    const report = document.createElement("div");
    report.className = "telem-block";
    const title = document.createElement("h3");
    title.className = "telem-title";
    title.textContent = "📊 Informe de sesión";
    report.appendChild(title);

    const rows = document.createElement("div");
    rows.className = "telem-rows";
    rows.appendChild(makeRow("Tiempo total", formatMMSS(data.totalTimeMs)));
    (data.levels || []).forEach(l => rows.appendChild(makeRow("Nivel " + l.level, formatMMSS(l.timeMs))));
    rows.appendChild(makeRow("Disparos", data.totalShots));
    rows.appendChild(makeRow("Aciertos", data.totalHits));
    rows.appendChild(makeRow("Fallos", data.totalMisses));
    rows.appendChild(makeRow("Precisión", data.accuracy + "%"));
    rows.appendChild(makeRow("Ángulo promedio", data.avgAngle + "°"));
    rows.appendChild(makeRow("Fuerza promedio", data.avgPower + "%"));
    rows.appendChild(makeRow("Gravedad final", Math.round(data.finalGravity) + " px/s²"));
    rows.appendChild(makeRow("Ajustes de gravedad", data.gravityChanges));
    rows.appendChild(makeRow("Reinicios", data.restarts));
    report.appendChild(rows);
    container.appendChild(report);

    // Bloque 2: análisis pedagógico
    const lines = window.Telemetry ? Telemetry.generateAnalysis(data) : [];
    if (lines.length) {
      const analysis = document.createElement("div");
      analysis.className = "telem-block telem-analysis";
      const t2 = document.createElement("h3");
      t2.className = "telem-title";
      t2.textContent = "💡 Análisis";
      analysis.appendChild(t2);
      lines.forEach(txt => {
        const p = document.createElement("p");
        p.textContent = txt;
        analysis.appendChild(p);
      });
      container.appendChild(analysis);
    }
  }

  function requestReward(level, score, finished, isWin, telemetry = null) {
    const sessionId = (telemetry && telemetry.sessionId) || currentSessionId();
    if (!sessionId) return;

    const headers = { "Content-Type": "application/json", "Accept": "application/json" };
    if (REWARD_CSRF) headers["X-CSRF-TOKEN"] = REWARD_CSRF;
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (metaCsrf && !headers["X-CSRF-TOKEN"]) headers["X-CSRF-TOKEN"] = metaCsrf.content;

    fetch(REWARD_ENDPOINT, {
      method: "POST",
      headers,
      credentials: "same-origin",
      body: JSON.stringify({
        level,
        score,
        finished,
        is_win: isWin,
        session_id: sessionId,
        telemetry
      })
    })
      .then(res => res.ok ? res.json() : null)
      .then(data => {
        if (!data) return;
        const delta = data.coins_delta ?? 0;
        if (delta > 0) {
          showBanner("+" + delta + " MONEDAS", data.capped ? "Tope diario parcial" : "¡Nivel superado!");
        } else if (data.duplicate) {
          showBanner("Recompensa ya registrada", "Sin monedas duplicadas");
        }
        if (window.parent && window.parent !== window) {
          window.parent.postMessage({ type: "projectiles:reward", coins_delta: delta }, window.location.origin);
        }
      })
      .catch(() => {});
  }

  function gameOver() {
    state = "OVER";
    saveBest();
    $("over-level").textContent = currentLevel + 1;
    $("over-score").textContent = score;
    $("over-best").textContent = bestScore;
    showScreen(screenOver);

    // 🔗 TELEMETRY: cerrar sesión, pintar informe y enviarlo con la recompensa
    let telemetry = window.Telemetry
      ? Telemetry.endSession({ result: "lose", score, levelReached: currentLevel + 1 })
      : null;
    if (telemetry && window.Telemetry) {
      telemetry.analysis = Telemetry.generateAnalysis(telemetry);
    }
    renderTelemetrySummary($("over-telemetry"), telemetry);
    requestReward(currentLevel + 1, score, true, false, telemetry);
  }

  function winGame() {
    state = "WIN";
    saveBest();
    $("win-score").textContent = score;
    $("win-best").textContent = bestScore;
    showScreen(screenWin);

    // 🔗 TELEMETRY: cerrar sesión, pintar informe y enviarlo con la recompensa
    let telemetry = window.Telemetry
      ? Telemetry.endSession({ result: "win", score, levelReached: currentLevel + 1 })
      : null;
    if (telemetry && window.Telemetry) {
      telemetry.analysis = Telemetry.generateAnalysis(telemetry);
    }
    renderTelemetrySummary($("win-telemetry"), telemetry);
    requestReward(currentLevel + 1, score, true, true, telemetry);
  }

  function spawnLevel() {
  aliens = [];
  const lvl = CONFIG.levels[currentLevel];
  const cols = Math.ceil(Math.sqrt(lvl.aliens * 1.5));
  const rows = Math.ceil(lvl.aliens / cols);
  const spacingX = 80;
  const spacingY = 55;               // un poco más juntos verticalmente
  const startX = (canvas.width - (cols - 1) * spacingX) / 2;

  // Empiezan más arriba (casi saliendo de la pantalla)
  const startY = 40;                 // ← cambia este valor si quieres más arriba o más abajo

  for (let r = 0; r < rows; r++) {
    for (let c = 0; c < cols; c++) {
      if (aliens.length >= lvl.aliens) break;
      aliens.push({
        x: startX + c * spacingX,
        y: startY + r * spacingY,    // ← ahora usan startY
        w: CONFIG.alienW,
        h: CONFIG.alienH,
        vx: (Math.random() - 0.5) * lvl.speed * 1.5,
        vy: Math.random() * 30 + 15,
        erraticTimer: Math.random() * 2
      });
    }
  }
  showBanner(`NIVEL ${currentLevel + 1}`, `${lvl.aliens} naves detectadas`);
  // 🔗 TELEMETRY: inicio de nivel
  if (window.Telemetry) Telemetry.recordLevelStart(currentLevel + 1);
}

  // Avanza de nivel (o gana) cuando ya no quedan aliens
  function checkLevelClear() {
    if (aliens.length > 0 || state !== "PLAYING") return;

    const clearedLevel = currentLevel + 1;
    const lastIndex = CONFIG.levels.length - 1;

    if (window.Telemetry) Telemetry.recordLevelEnd(clearedLevel);

    if (currentLevel < lastIndex) {
      const partialTelemetry = window.Telemetry ? Telemetry.getSnapshot() : null;
      requestReward(clearedLevel, score, false, true, partialTelemetry);
      currentLevel++;
      spawnLevel();
      updateHUD();
    } else {
      winGame();
    }
  }

  function shoot() {
    if (state !== "PLAYING") return;

    fireZone.classList.add('discharge');
    setTimeout(() => fireZone.classList.remove('discharge'), 550);

    const angleRad = (ship.angle - 90) * Math.PI / 180;
    const speed = ship.power * CONFIG.bulletSpeedMult;

    const vx = Math.sin(angleRad) * speed;
    const vy = -Math.cos(angleRad) * speed;

    // Mismo pivote que se usa al dibujar el cañón
    const pivotX = ship.x;
    const pivotY = ship.y - CONFIG.shipH * CONFIG.cannonPivot;

    const startX = pivotX + Math.sin(angleRad) * CONFIG.cannonLength;
    const startY = pivotY - Math.cos(angleRad) * CONFIG.cannonLength;

        bullets.push({ x: startX, y: startY, vx, vy, trail: [], time: 0, t: 0 });

    // 🔗 TELEMETRY: guardar ángulo y fuerza del disparo en la bala (los sliders pueden cambiar mientras vuela)
    const firedBullet = bullets[bullets.length - 1];
    firedBullet.shotAngle = ship.angle;
    firedBullet.shotPower = ship.power;
  }

  function spawnParticles(x, y, color) {
    for (let i = 0; i < 12; i++) {
      const angle = Math.random() * Math.PI * 2;
      const speed = Math.random() * 180 + 40;
      particles.push({ x, y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed, life: 1, color });
    }
  }

  // --- ACTUALIZACIÓN (física y colisiones, SIN dibujar) ---
  function update(dt) {
    // 1. ALIENS: movimiento errático individual
    const lvl = CONFIG.levels[currentLevel];
    for (const a of aliens) {
      a.erraticTimer -= dt;

      if (a.erraticTimer <= 0) {
        a.erraticTimer = Math.random() * 2.5 + 0.5;

        if (Math.random() < 0.3) {
          a.vy = lvl.speed * (Math.random() * 1.5 + 0.5); // picado
        } else {
          a.vy = Math.random() * 30 + 15;
        }
        a.vx = (Math.random() - 0.5) * lvl.speed * 2.5;
      }

      a.x += a.vx * dt;
      a.y += a.vy * dt;

      if (a.x < 30) { a.x = 30; a.vx = Math.abs(a.vx); }
      if (a.x > canvas.width - 30) { a.x = canvas.width - 30; a.vx = -Math.abs(a.vx); }
    }

    // 2. Limpiar aliens que salieron por abajo
    for (let i = aliens.length - 1; i >= 0; i--) {
      if (aliens[i].y > canvas.height + 50) aliens.splice(i, 1);
    }
    updateHUD();
    checkLevelClear();
    if (state !== "PLAYING") return;

    // 3. BALAS (movimiento parabólico)
    for (let i = bullets.length - 1; i >= 0; i--) {
      const b = bullets[i];
      b.time += dt;

            b.x += b.vx * dt;
      b.vy += getEffectiveGravity() * dt;
      b.y += b.vy * dt;
      b.t += dt;

      b.trail.push({ x: b.x, y: b.y });
      if (b.trail.length > CONFIG.trailLen) b.trail.shift();

      if (b.y > canvas.height || b.x < -20 || b.x > canvas.width + 20) {
        // 🔗 TELEMETRY: disparo fallido (salió de pantalla)
        if (window.Telemetry) Telemetry.recordShot({ angle: b.shotAngle, power: b.shotPower, hit: false });
        bullets.splice(i, 1);
        continue;
      }

      for (let j = aliens.length - 1; j >= 0; j--) {
        const a = aliens[j];
        if (Math.hypot(b.x - a.x, b.y - a.y) < 35) {
          spawnParticles(a.x, a.y, "#ff2bd6");
          // 🔗 TELEMETRY: disparo con impacto
          if (window.Telemetry) Telemetry.recordShot({ angle: b.shotAngle, power: b.shotPower, hit: true });
          aliens.splice(j, 1);
          bullets.splice(i, 1);
          score += (currentLevel + 1) * 100;
          updateHUD();
          checkLevelClear();
          break;
        }
      }
      if (state !== "PLAYING") return;
    }

    // 4. DERROTA: un alien toca alguna hitbox de la nave
    const shipBoxes = getShipBoxes();
    for (const a of aliens) {
      const ab = getAlienBox(a);
      if (shipBoxes.some(box => boxesOverlap(ab, box))) {
        spawnParticles(ship.x, ship.y, "#c5d912");
        gameOver();
        return;
      }
    }

    // 5. Partículas
    for (let i = particles.length - 1; i >= 0; i--) {
      const p = particles[i];
      p.x += p.vx * dt;
      p.y += p.vy * dt;
      p.life -= dt * 2;
      if (p.life <= 0) particles.splice(i, 1);
    }
  }

  // --- RENDER (solo dibuja) ---
  function render() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Fondo (cubierto totalmente)
    if (bgImg.complete && bgImg.naturalHeight !== 0) {
      const imgRatio = bgImg.naturalWidth / bgImg.naturalHeight;
      const canvasRatio = canvas.width / canvas.height;
      let dw, dh, dx, dy;
      if (imgRatio > canvasRatio) { dh = canvas.height; dw = dh * imgRatio; dx = (canvas.width - dw) / 2; dy = 0; }
      else { dw = canvas.width; dh = dw / imgRatio; dx = 0; dy = (canvas.height - dh) / 2; }
      ctx.drawImage(bgImg, dx, dy, dw, dh);
    } else {
      ctx.fillStyle = "#05030f";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Nave jugador (base gigante) + cañón
    ctx.save();
    ctx.translate(ship.x, ship.y);

    if (shipImg.complete && shipImg.naturalHeight !== 0) {
      ctx.drawImage(shipImg, -CONFIG.shipW / 2, -CONFIG.shipH / 2, CONFIG.shipW, CONFIG.shipH);
    } else {
      ctx.fillStyle = "#19f2ff";
      ctx.fillRect(-CONFIG.shipW / 2, -CONFIG.shipH / 2, CONFIG.shipW, CONFIG.shipH);
    }

    // Pivote del cañón y rotación (apunta en la misma dirección que la bala)
    ctx.translate(0, -CONFIG.shipH * CONFIG.cannonPivot);
    ctx.rotate((ship.angle - 90) * Math.PI / 180);

    // 🔗 TORRETA: sprite en lugar de los dos fillRect (con fallback si la imagen no cargó)
    if (torretImg.complete && torretImg.naturalHeight !== 0) {
      ctx.rotate(-Math.PI / 2);          // el PNG apunta a la derecha; lo giramos para que apunte "arriba"
      if (ship.angle < 90) ctx.scale(1, -1); // al apuntar a la izquierda, voltear para que no quede boca abajo
      ctx.drawImage(
        torretImg,
        -CONFIG.cannonSpriteW * CONFIG.cannonSpritePivotX,
        -CONFIG.cannonSpriteH * CONFIG.cannonSpritePivotY + CONFIG.cannonSpriteOffsetY,
        CONFIG.cannonSpriteW,
        CONFIG.cannonSpriteH
      );
    } else {
      ctx.fillStyle = "#19f2ff"; ctx.shadowColor = "#19f2ff"; ctx.shadowBlur = 10;
      ctx.fillRect(-5, -45, 10, 45);
      ctx.fillStyle = "#7a4dff"; ctx.shadowColor = "#7a4dff";
      ctx.fillRect(-7, -54, 14, 14);
      ctx.shadowBlur = 0;
    }
    ctx.restore();

    // Estelas y balas
    bullets.forEach(b => {
      if (b.trail.length > 1) {
        ctx.beginPath();
        ctx.moveTo(b.trail[0].x, b.trail[0].y);
        for (let k = 1; k < b.trail.length; k++) ctx.lineTo(b.trail[k].x, b.trail[k].y);
        ctx.strokeStyle = "rgba(25, 242, 255, 0.35)";
        ctx.lineWidth = 2;
        ctx.stroke();
      }
      ctx.beginPath();
      ctx.arc(b.x, b.y, CONFIG.bulletR, 0, Math.PI * 2);
      ctx.fillStyle = "#eaf8ff";
      ctx.shadowColor = "#19f2ff"; ctx.shadowBlur = 12;
      ctx.fill();
      ctx.shadowBlur = 0;
    });

    // Aliens
    if (alienImg.complete) {
      aliens.forEach(a => {
        ctx.drawImage(alienImg, a.x - a.w / 2, a.y - a.h / 2, a.w, a.h);
      });
    }

        // Partículas
    particles.forEach(p => {
      ctx.globalAlpha = p.life;
      ctx.fillStyle = p.color;
      ctx.beginPath();
      ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
      ctx.fill();
    });
    ctx.globalAlpha = 1;

    // 🔗 PEDAGOGY: overlay pedagógico
    if (window.PedagogyOverlay && state === "PLAYING") {
      const shot = getCurrentShot();

      // Trayectoria predicha + ticks
      if (shot && pedagogyState.showTrajectory) {
        PedagogyOverlay.drawPredictedTrajectory(ctx, shot.trajectory);
        if (pedagogyState.showTicks) {
          PedagogyOverlay.drawTimeTicks(ctx, shot.trajectory, 0.25);
        }
      }

      // Vector v₀ en el cañón
      if (shot && pedagogyState.showVectors) {
        const pivotX = ship.x;
        const pivotY = ship.y - CONFIG.shipH * CONFIG.cannonPivot;
        PedagogyOverlay.drawV0Vector(ctx, pivotX, pivotY, shot.vx, shot.vy, { label: "v₀" });
      }

      // Vectores vx / vy sobre cada bala + panel de datos
      bullets.forEach(b => {
        if (pedagogyState.showVectors) {
          PedagogyOverlay.drawComponentVectors(ctx, b.x, b.y, b.vx, b.vy);
        }
        if (pedagogyState.showLiveData) {
          PedagogyOverlay.drawLiveDataBox(ctx, {
            t: b.t || 0,
            x: b.x,
            y: b.y,
            vx: b.vx,
            vy: b.vy
          }, { x: 20, y: 120 });
        }
      });
    }
  }

  // --- BUCLE PRINCIPAL ---
  function gameLoop(timestamp) {
    requestAnimationFrame(gameLoop);
    if (state !== "PLAYING") { lastTime = timestamp; return; }

    const dt = Math.min((timestamp - lastTime) / 1000, 0.05);
    lastTime = timestamp;

    update(dt);
    render();
  }

    // --- ARRANQUE SEGURO ---
  initUI();
  requestAnimationFrame(gameLoop);

  // 🔗 PEDAGOGY: montar panel + estado local + helpers
  const pedagogyState = {
    showTrajectory: true,
    showTicks: true,
    showVectors: true,
    showLiveData: true
  };

  if (window.PedagogyPanel) {
    PedagogyPanel.mount(document.body);
    PedagogyPanel.on("flags", (f) => {
      pedagogyState.showTrajectory = f.showTrajectory;
      pedagogyState.showTicks      = f.showTicks;
      pedagogyState.showVectors    = f.showVectors;
      pedagogyState.showLiveData   = f.showLiveData;
    });
    PedagogyPanel.on("gravity", () => refreshPedagogyPanel());
    console.log("[pedagogy] panel montado");
  }

  function getEffectiveGravity() {
    return window.PedagogyPanel ? PedagogyPanel.getGravity() : CONFIG.gravity;
  }

  function getCurrentShot() {
    if (!window.PhysicsEngine) return null;
    const pivotX = ship.x;
    const pivotY = ship.y - CONFIG.shipH * CONFIG.cannonPivot;
    return PhysicsEngine.computeShot({
      angleDeg: ship.angle,
      power: ship.power,
      gravity: getEffectiveGravity(),
      bulletSpeedMult: CONFIG.bulletSpeedMult,
      pivotX,
      pivotY,
      cannonLength: CONFIG.cannonLength,
      yFloor: canvas.height,
      steps: 80
    });
  }

  function refreshPedagogyPanel() {
    if (!window.PedagogyPanel || !window.PhysicsEngine) return;
    const shot = getCurrentShot();
    if (!shot) return;
    PedagogyPanel.updatePanel({
      angleDeg: shot.angleDeg,
      v0: shot.v0,
      vx: shot.vx,
      vy: shot.vy,
      timeOfFlight: shot.timeOfFlight,
      range: shot.range,
      maxHeight: shot.maxHeight
    });
  }

})();