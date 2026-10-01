(function () {
  function readConfig() {
    const root = document.getElementById('crossword-app');
    if (!root) {
      throw new Error('No se encontró #crossword-app');
    }
    return {
      levelUrl: root.dataset.levelUrl,
      progressUrl: root.dataset.progressUrl,
      saveUrl: root.dataset.saveUrl,
      resetUrl: root.dataset.resetUrl,
    };
  }

  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  async function request(url, options = {}) {
    const response = await fetch(url, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {}),
      },
      ...options,
    });

    if (!response.ok) {
      const text = await response.text();
      throw new Error(text || 'Error de red');
    }

    return response.json();
  }

  window.CrosswordApi = {
    async getLevel() {
      const cfg = readConfig();
      return request(cfg.levelUrl);
    },
    async getProgress() {
      const cfg = readConfig();
      return request(cfg.progressUrl);
    },
    async saveProgress(action, data = {}) {
      const cfg = readConfig();
      return request(cfg.saveUrl, {
        method: 'POST',
        body: JSON.stringify({ action, ...data }),
      });
    },
    async resetProgress() {
      const cfg = readConfig();
      return request(cfg.resetUrl, { method: 'POST', body: JSON.stringify({}) });
    },
  };
})();
