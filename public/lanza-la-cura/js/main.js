/* main.js — Arranque y bucle principal. */
(function () {
  let ultimo = 0;
  let rewardUrl = '';
  let statusUrl = '';
  let csrfToken = '';

  function readIntegrationParams() {
    const params = new URLSearchParams(window.location.search);
    rewardUrl = params.get('reward_url') || '';
    statusUrl = params.get('status_url') || '';
    csrfToken = params.get('csrf') || '';
  }

  function fetchStatus() {
    if (!statusUrl) return;
    fetch(statusUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    })
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => {
        if (data && typeof UI !== 'undefined' && UI.actualizarMonedas) {
          UI.actualizarMonedas(data);
        }
      })
      .catch(() => {});
  }

  function applyRewardResponse(data) {
    if (!data) return;
    if (typeof UI !== 'undefined' && UI.actualizarMonedas) {
      UI.actualizarMonedas({
        coins: data.total_coins ?? data.coins,
        xp: data.xp,
        daily_earned: data.daily_earned,
        daily_cap: data.daily_cap,
      });
    }
    fetchStatus();
    if (window.parent && window.parent !== window) {
      window.parent.postMessage(
        { type: 'cure:reward', coins_delta: data.coins_delta ?? 0 },
        window.location.origin,
      );
    }
    console.log('[cure] recompensa OK', data.coins_delta, 'monedas');
  }

  // 🔗 MONEDAS
  window.sendReward = function sendReward(telemetry, opts = {}) {
    if (!rewardUrl || !telemetry) return;
    const headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
    const body = JSON.stringify({ telemetry });

    if (opts.beacon) {
      fetch(rewardUrl, {
        method: 'POST',
        headers,
        credentials: 'same-origin',
        body,
        keepalive: true,
      }).catch(() => {});
      return;
    }

    fetch(rewardUrl, {
      method: 'POST',
      headers,
      credentials: 'same-origin',
      body,
    })
      .then(async (res) => (res.ok ? res.json() : null))
      .then(applyRewardResponse)
      .catch((err) => {
        console.warn('[cure] reward fetch error', err);
      });
  };

  function loop(timestamp) {
    const dt = Math.min((timestamp - ultimo) / 1000, 0.05);
    ultimo = timestamp;
    Estado.tiempo = timestamp;

    if (Estado.enJuego) {
      Game.animar();
      Fisica.actualizar(dt);
      Estado.paciente.animando = Estado.paciente.herido;
      Lienzo.render();
    }

    requestAnimationFrame(loop);
  }

  function init() {
    Meta.cargar();
    readIntegrationParams();
    fetchStatus();
    Lienzo.init();

    document.getElementById('btn-comenzar').addEventListener('click', () => Game.iniciar());
    document.getElementById('btn-verificar').addEventListener('click', () => Game.verificarYLanzar());
    document.getElementById('btn-reiniciar').addEventListener('click', () => Game.reiniciar());
    document.getElementById('btn-jugar-de-nuevo').addEventListener('click', () => Game.reiniciar());
    document.getElementById('btn-pista').addEventListener('click', () => Game.usarPista());
    document.getElementById('btn-trayectoria').addEventListener('click', () => Game.previewTrayectoria());

    const input = document.getElementById('entrada-respuesta');
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') Game.verificarYLanzar();
    });
    input.addEventListener('input', () => {
      const valor = parseFloat(String(input.value).replace(',', '.'));
      UI.actualizarProximidadInput(valor);
    });

    window.addEventListener('pagehide', () => {
      if (typeof window.cureAbandonIfNeeded === 'function') {
        window.cureAbandonIfNeeded();
      }
    });

    requestAnimationFrame(loop);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
