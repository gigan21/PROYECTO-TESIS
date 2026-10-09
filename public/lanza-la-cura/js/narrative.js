/* narrative.js — Textos narrativos cyberpunk. */
const Narrativa = {
  TEXTO_VICTORIA: '¡Cura administrada! Lyra respira. Neo-San Rafael tiene esperanza.',

  nombreNivel(n) {
    const map = { 1: 'Azotea', 2: 'Callejón', 3: 'Puente', 4: 'Torre final' };
    return map[n] || `Nivel ${n}`;
  },

  textoAvance(siguienteNivel) {
    return `¡Lyra avanza a ${this.nombreNivel(siguienteNivel)}!`;
  },

  textoFalloNivel(nivel, dosisRestantes) {
    if (dosisRestantes <= 1) return 'Lyra se debilita… esto es crítico.';
    if (dosisRestantes === 2) return `Lyra se aleja. Quedan ${dosisRestantes} dosis.`;
    return `Lyra se debilita… Quedan ${dosisRestantes} dosis.`;
  },

  finalVictoria(tituloRango) {
    return `Completaste los 4 niveles. Rango: ${tituloRango}.`;
  },

  finalDerrota() {
    return 'Lyra resistió, pero necesitas más entrenamiento. Repasa las fórmulas e inténtalo de nuevo.';
  },
};
