/* main.js — Arranque y bucle principal. */
(function () {
  let ultimo = 0;

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

    requestAnimationFrame(loop);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
