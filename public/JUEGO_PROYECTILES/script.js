// =========================================================================
// ARTILLERÍA ORBITAL - LÓGICA COMPLETA CORREGIDA
// =========================================================================

(() => {
  "use strict";

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
      { aliens: 24, speed: 65, descent: 25 },
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

  function gameOver() {
    state = "OVER";
    saveBest();
    $("over-level").textContent = currentLevel + 1;
    $("over-score").textContent = score;
    $("over-best").textContent = bestScore;
    showScreen(screenOver);
  }

  function winGame() {
    state = "WIN";
    saveBest();
    $("win-score").textContent = score;
    $("win-best").textContent = bestScore;
    showScreen(screenWin);
  }

  function spawnLevel() {
    aliens = [];
    const lvl = CONFIG.levels[currentLevel];
    const cols = Math.ceil(Math.sqrt(lvl.aliens * 1.5));
    const rows = Math.ceil(lvl.aliens / cols);
    const spacingX = 80;
    const spacingY = 60;
    const startX = (canvas.width - (cols - 1) * spacingX) / 2;

    for (let r = 0; r < rows; r++) {
      for (let c = 0; c < cols; c++) {
        if (aliens.length >= lvl.aliens) break;
        aliens.push({
          x: startX + c * spacingX,
          y: 70 + r * spacingY,
          w: CONFIG.alienW,
          h: CONFIG.alienH,
          vx: (Math.random() - 0.5) * lvl.speed * 1.5,
          vy: Math.random() * 30 + 15,
          erraticTimer: Math.random() * 2
        });
      }
    }
    showBanner(`NIVEL ${currentLevel + 1}`, `${lvl.aliens} naves detectadas`);
  }

  // Avanza de nivel (o gana) cuando ya no quedan aliens
  function checkLevelClear() {
    if (aliens.length > 0 || state !== "PLAYING") return;
    if (currentLevel < CONFIG.levels.length - 1) {
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

    bullets.push({ x: startX, y: startY, vx, vy, trail: [], time: 0 });
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
      b.vy += CONFIG.gravity * dt;
      b.y += b.vy * dt;

      b.trail.push({ x: b.x, y: b.y });
      if (b.trail.length > CONFIG.trailLen) b.trail.shift();

      if (b.y > canvas.height || b.x < -20 || b.x > canvas.width + 20) {
        bullets.splice(i, 1);
        continue;
      }

      for (let j = aliens.length - 1; j >= 0; j--) {
        const a = aliens[j];
        if (Math.hypot(b.x - a.x, b.y - a.y) < 35) {
          spawnParticles(a.x, a.y, "#ff2bd6");
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

    ctx.fillStyle = "#19f2ff"; ctx.shadowColor = "#19f2ff"; ctx.shadowBlur = 10;
    ctx.fillRect(-5, -45, 10, 45);
    ctx.fillStyle = "#7a4dff"; ctx.shadowColor = "#7a4dff";
    ctx.fillRect(-7, -54, 14, 14);
    ctx.shadowBlur = 0;
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

})();