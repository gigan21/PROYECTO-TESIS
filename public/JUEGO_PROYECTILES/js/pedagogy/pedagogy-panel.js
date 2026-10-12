// =========================================================================
// PEDAGOGY PANEL — Panel lateral de datos en vivo + Modo laboratorio
// =========================================================================
// Depende de: physics-engine.js (para nada directo, pero por coherencia)
// No conoce el juego. Expone una API para engancharlo desde script.js.
// =========================================================================

(() => {
  "use strict";

  // --- CONFIGURACIÓN ---
  const DEFAULTS = Object.freeze({
    gravity: 320,
    collapsed: false
  });

  // --- ESTADO INTERNO ---
  const state = {
    gravity: DEFAULTS.gravity,
    collapsed: DEFAULTS.collapsed,
    flags: {
      showTrajectory: true,
      showTicks: true,
      showVectors: true,
      showLiveData: true,
      labMode: false
    },
    listeners: {
      gravity: [],
      flags: [],
      collapse: []
    }
  };

  // --- REFS DOM (se rellenan en mount) ---
  let el = null;

  // --- PLANTILLA HTML ---
  function template() {
    return `
<div class="pdp" id="pdp-panel" role="region" aria-label="Panel de física">
  <div class="pdp-head">
    <span class="pdp-title">📊 Panel de física</span>
    <button class="pdp-close" id="pdp-close" aria-label="Ocultar panel">×</button>
  </div>

  <!-- Datos del disparo actual -->
  <div class="pdp-section">
    <div class="pdp-section-title">Disparo actual</div>
    <div class="pdp-row"><span class="k">Ángulo θ</span>       <span class="v" id="pdp-angle">—</span></div>
    <div class="pdp-row"><span class="k">Velocidad v₀</span>   <span class="v" id="pdp-v0">—</span></div>
    <div class="pdp-row"><span class="k">Componente vx</span>  <span class="v" id="pdp-vx">—</span></div>
    <div class="pdp-row"><span class="k">Componente vy</span>  <span class="v" id="pdp-vy">—</span></div>
  </div>

  <!-- Predicciones teóricas -->
  <div class="pdp-section">
    <div class="pdp-section-title">Predicción teórica</div>
    <div class="pdp-row"><span class="k">Tiempo de vuelo</span><span class="v hot"  id="pdp-t">—</span></div>
    <div class="pdp-row"><span class="k">Alcance</span>          <span class="v hot"  id="pdp-range">—</span></div>
    <div class="pdp-row"><span class="k">Altura máxima</span>    <span class="v cool" id="pdp-hmax">—</span></div>
  </div>

  <!-- Modo laboratorio -->
  <div class="pdp-section">
    <div class="pdp-section-title">🧪 Modo laboratorio</div>
    <div class="pdp-row"><span class="k">Gravedad (g)</span>
      <span class="g-val" id="pdp-g-value">320</span>
    </div>
    <div class="pdp-gravity">
      <input type="range" id="pdp-gravity" min="80" max="600" step="10" value="320">
    </div>

    <div class="pdp-toggles" style="margin-top:10px;">
      <label class="pdp-toggle"><input type="checkbox" id="pdp-flag-trajectory" checked><span>Trayectoria predicha</span></label>
      <label class="pdp-toggle"><input type="checkbox" id="pdp-flag-ticks" checked><span>Ticks de tiempo</span></label>
      <label class="pdp-toggle"><input type="checkbox" id="pdp-flag-vectors" checked><span>Vectores (v₀, vx, vy)</span></label>
      <label class="pdp-toggle"><input type="checkbox" id="pdp-flag-livedata" checked><span>Datos en vivo de la bala</span></label>
    </div>
  </div>
</div>

<button class="pdp-reopen" id="pdp-reopen" aria-label="Mostrar panel">📊</button>
    `;
  }

  // --- MOUNT ---
  function mount(target = document.body) {
    // Inyectar CSS si no está
       // Inyectar CSS si no está
    if (!document.getElementById("pdp-styles")) {
      const linkEl = document.createElement("link");
      linkEl.id = "pdp-styles";
      linkEl.rel = "stylesheet";
      linkEl.href = "js/pedagogy/pedagogy-panel.css";
      document.head.appendChild(linkEl);
    }

    // Inyectar HTML
    const container = document.createElement("div");
    container.innerHTML = template().trim();
    while (container.firstChild) target.appendChild(container.firstChild);

    // Refs
    el = {
      panel:     document.getElementById("pdp-panel"),
      close:     document.getElementById("pdp-close"),
      reopen:    document.getElementById("pdp-reopen"),

      angle:     document.getElementById("pdp-angle"),
      v0:        document.getElementById("pdp-v0"),
      vx:        document.getElementById("pdp-vx"),
      vy:        document.getElementById("pdp-vy"),
      t:         document.getElementById("pdp-t"),
      range:     document.getElementById("pdp-range"),
      hmax:      document.getElementById("pdp-hmax"),

      gravity:   document.getElementById("pdp-gravity"),
      gValue:    document.getElementById("pdp-g-value"),

      flagTraj:  document.getElementById("pdp-flag-trajectory"),
      flagTicks: document.getElementById("pdp-flag-ticks"),
      flagVec:   document.getElementById("pdp-flag-vectors"),
      flagLive:  document.getElementById("pdp-flag-livedata")
    };

    bindEvents();
    return el.panel;
  }

  // --- EVENTOS ---
  function bindEvents() {
    el.close.addEventListener("click", () => setCollapsed(true));
    el.reopen.addEventListener("click", () => setCollapsed(false));

    el.gravity.addEventListener("input", () => {
      state.gravity = parseFloat(el.gravity.value);
      el.gValue.textContent = state.gravity.toFixed(0);
      emit("gravity", state.gravity);
      if (window.Telemetry) Telemetry.recordGravityChange(state.gravity); // 🔗 TELEMETRY
    });

    const flagMap = {
      flagTraj:  "showTrajectory",
      flagTicks: "showTicks",
      flagVec:   "showVectors",
      flagLive:  "showLiveData"
    };
    Object.entries(flagMap).forEach(([domKey, flagKey]) => {
      el[domKey].addEventListener("change", () => {
        state.flags[flagKey] = el[domKey].checked;
        emit("flags", getFlags());
      });
    });
  }

  // --- EVENT EMITTER SIMPLE ---
  function on(event, cb) {
    if (state.listeners[event]) state.listeners[event].push(cb);
  }
  function emit(event, payload) {
    (state.listeners[event] || []).forEach(cb => cb(payload));
  }

  // --- API PÚBLICA ---
  function updatePanel(data) {
    if (!el) return;
    const fmt = (n, d = 1) => Number.isFinite(n) ? n.toFixed(d) : "—";

    if (data.angleDeg !== undefined) el.angle.textContent = data.angleDeg.toFixed(0) + "°";
    if (data.v0 !== undefined)       el.v0.textContent    = fmt(data.v0);
    if (data.vx !== undefined)       el.vx.textContent    = fmt(data.vx);
    if (data.vy !== undefined)       el.vy.textContent    = fmt(data.vy);
    if (data.timeOfFlight !== undefined) el.t.textContent = fmt(data.timeOfFlight, 2) + " s";
    if (data.range !== undefined)    el.range.textContent = fmt(data.range, 0) + " px";
    if (data.maxHeight !== undefined) el.hmax.textContent = fmt(data.maxHeight, 0) + " px";
  }

  function getGravity() { return state.gravity; }

  function setGravity(g) {
    state.gravity = g;
    if (el) {
      el.gravity.value = g;
      el.gValue.textContent = g.toFixed(0);
    }
  }

  function getFlags() { return { ...state.flags }; }

  function setFlag(name, value) {
    if (name in state.flags) {
      state.flags[name] = value;
      emit("flags", getFlags());
    }
  }

  function setCollapsed(v) {
    state.collapsed = v;
    if (!el) return;
    el.panel.classList.toggle("collapsed", v);
    el.reopen.classList.toggle("show", v);
    emit("collapse", v);
  }

  function toggle() { setCollapsed(!state.collapsed); }

  // --- EXPORTACIÓN ---
  const PedagogyPanel = {
    mount,
    updatePanel,
    getGravity,
    setGravity,
    getFlags,
    setFlag,
    setCollapsed,
    toggle,
    on,
    _state: state   // por si necesitas inspeccionar en devtools
  };

  window.PedagogyPanel = PedagogyPanel;
})();