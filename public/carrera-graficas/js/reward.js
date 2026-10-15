/* reward.js — Monedas / XP Carrera de Gráficas */
(() => {
  'use strict';

  let rewardUrl = '';
  let statusUrl = '';
  let csrfToken = '';

  function readParams() {
    const params = new URLSearchParams(window.location.search);
    rewardUrl = params.get('reward_url') || '';
    statusUrl = params.get('status_url') || '';
    csrfToken = params.get('csrf') || '';
  }

  function updateHud(data) {
    if (!data) return;
    const coinsEl = document.getElementById('hud-coins');
    const xpEl = document.getElementById('hud-xp');
    const dailyEl = document.getElementById('hud-daily');
    if (coinsEl && data.coins != null) coinsEl.textContent = String(data.coins);
    if (xpEl && data.xp != null) xpEl.textContent = String(data.xp);
    if (dailyEl && data.daily_earned != null && data.daily_cap != null) {
      dailyEl.textContent = `${data.daily_earned}/${data.daily_cap} hoy`;
    }
  }

  function showToast(msg) {
    const el = document.getElementById('reward-toast');
    if (!el) return;
    el.textContent = msg;
    el.hidden = false;
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => {
      el.hidden = true;
    }, 3500);
  }

  // 🔗 MONEDAS CARRERA
  function fetchStatus() {
    if (!statusUrl) return;
    fetch(statusUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    })
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => updateHud(data))
      .catch(() => {});
  }

  function sendReward(telemetry) {
    if (!rewardUrl || !telemetry) return Promise.resolve(null);

    const headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    return fetch(rewardUrl, {
      method: 'POST',
      headers,
      credentials: 'same-origin',
      body: JSON.stringify({ telemetry }),
    })
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => {
        if (!data) return null;
        updateHud(data);
        if (data.duplicate) {
          showToast('Recompensa ya registrada para esta partida.');
        } else if ((data.coins_delta ?? 0) > 0 || (data.xp_delta ?? 0) > 0) {
          showToast(`+${data.coins_delta ?? 0} monedas · +${data.xp_delta ?? 0} XP`);
        } else if (data.capped) {
          showToast('Tope diario de monedas alcanzado.');
        }
        if (window.parent && window.parent !== window) {
          window.parent.postMessage(
            { type: 'chart_race:reward', coins_delta: data.coins_delta ?? 0 },
            window.location.origin,
          );
        }
        return data;
      })
      .catch(() => null);
  }

  readParams();
  document.addEventListener('DOMContentLoaded', fetchStatus);

  window.ChartRaceReward = {
    fetchStatus,
    sendReward,
  };
})();
