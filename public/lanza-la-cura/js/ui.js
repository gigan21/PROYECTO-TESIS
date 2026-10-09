/* ui.js — Actualización de la interfaz (HUD, feedback, pantallas). */
const UI = (() => {
  const $ = (id) => document.getElementById(id);
  let resolucionTimers = [];

  function limpiarResolucionTimers() {
    resolucionTimers.forEach(clearTimeout);
    resolucionTimers = [];
  }

  function programarUI(ms, fn) {
    const sesion = Estado.sesion;
    const t = setTimeout(() => { if (sesion === Estado.sesion) fn(); }, ms);
    resolucionTimers.push(t);
  }

  function actualizarProgresoNiveles() {
    const wrap = $('hud-progreso');
    if (!wrap) return;
    wrap.textContent = '';
    for (let i = 1; i <= 4; i++) {
      const dot = document.createElement('span');
      dot.className = 'hud-progreso-dot';
      if (i < Estado.nivelActual) dot.classList.add('completado');
      else if (i === Estado.nivelActual) dot.classList.add('actual');
      wrap.appendChild(dot);
    }
    const nom = $('hud-nivel-nombre');
    if (nom && Estado.nivelConfig) {
      nom.textContent = Estado.nivelConfig.nombre;
    }
  }

  function actualizarHUD() {
    $('hud-ronda').textContent = `NIVEL ${Estado.nivelActual}/4`;
    $('hud-puntuacion').textContent = Math.round(Estado.puntuacion);
    $('hud-racha').textContent = `×${Estado.racha}`;
    const restantes = 4 - (Estado.nivelActual - 1);
    if ($('hud-restantes')) {
      $('hud-restantes').textContent = String(restantes);
    }
    const lyra = Fisica.lyraPosicion();
    const dist = Math.abs(lyra.x - Fisica.puntoLanzamiento().x) / CONFIG.ESCALA;
    $('hud-distancia').textContent = `${dist.toFixed(1)} m`;

    const wrap = $('hud-corazones');
    wrap.textContent = '';
    for (let i = 0; i < CONFIG.DOSIS_MAX; i++) {
      const span = document.createElement('span');
      span.className = 'hud-corazon' + (Estado.corazonesPerdidos[i] ? ' perdido' : '');
      span.setAttribute('aria-hidden', 'true');
      wrap.appendChild(span);
    }
    actualizarProgresoNiveles();
  }

  function aplicarNivel(nivel, opts = {}) {
    const fondo = $('fondo-nivel');
    if (!fondo) return;
    const url = `url('${nivel.fondo}')`;
    if (opts.transicion && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      fondo.classList.add('fading');
      setTimeout(() => {
        fondo.style.backgroundImage = url;
        fondo.classList.remove('fading');
      }, CONFIG.T_FONDO_FADE / 2);
    } else {
      fondo.style.backgroundImage = url;
    }

    const aviso = $('panel-aviso-nivel');
    if (aviso) {
      if (nivel.id === 4) {
        aviso.textContent = '⚠️ Evita la zona roja. Puedes ver la trayectoria prevista.';
        aviso.classList.remove('oculto');
      } else {
        aviso.textContent = '';
        aviso.classList.add('oculto');
      }
    }

    const btnTray = $('btn-trayectoria');
    if (btnTray) {
      btnTray.classList.toggle('oculto', nivel.id !== 4);
    }
  }

  function setNarrativa(texto) {
    $('narrativa-canvas').textContent = texto || '';
  }

  function actualizarAmbiente(ronda) {
    const v = $('overlay-viento');
    const g = $('overlay-gravedad');
    v.classList.toggle('oculto', !ronda?.flags?.viento);
    g.classList.toggle('oculto', !ronda?.flags?.gravedadAlta);
    v.setAttribute('aria-hidden', String(!ronda?.flags?.viento));
    g.setAttribute('aria-hidden', String(!ronda?.flags?.gravedadAlta));
  }

  function mostrarRonda(ronda, opts = {}) {
    const panel = $('panel-control');
    const aplicar = () => {
      const f = ronda.formula;
      $('ronda-tag').textContent = `NIVEL ${Estado.nivelActual} / 4 · ${ronda.nombreNivel || ''}`;
      $('grupo-formula').textContent = f.grupo;
      const ft = $('formula-texto');
      ft.textContent = f.texto;
      if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        ft.classList.remove('glitch-once');
        void ft.offsetWidth;
        ft.classList.add('glitch-once');
      }
      $('etiqueta-respuesta').textContent = `Calcula ${f.magnitud}`;
      $('unidad-respuesta').textContent = f.unidad;

      const lista = $('datos-ronda');
      lista.textContent = '';
      ronda.datos.forEach((d) => {
        const li = document.createElement('li');
        li.innerHTML = `<span class="dato-simbolo">${d.s}</span><span class="dato-nombre">${d.n}</span><span class="dato-valor">${d.v.toFixed(d.dec)} ${d.u}</span>`;
        lista.appendChild(li);
      });

      if (ronda.flags?.viento) {
        const li = document.createElement('li');
        li.innerHTML = `<span class="dato-simbolo">🌪️</span><span class="dato-nombre">viento</span><span class="dato-valor">${ronda.viento >= 0 ? '+' : ''}${ronda.viento.toFixed(1)} m/s²</span>`;
        lista.appendChild(li);
      }
      if (ronda.flags?.gravedadAlta) {
        const li = document.createElement('li');
        li.innerHTML = `<span class="dato-simbolo">g</span><span class="dato-nombre">gravedad</span><span class="dato-valor">${ronda.gRonda.toFixed(2)} m/s²</span>`;
        lista.appendChild(li);
      }

      const input = $('entrada-respuesta');
      input.value = '';
      input.className = '';
      $('entrada-grupo').className = 'entrada-grupo';
      $('estado-calculo').textContent = '';
      $('estado-calculo').className = 'estado-calculo';
      bloquearEntrada(false);
      setLanzando(false);
      actualizarAmbiente(ronda);
      Estado.trayectoriaPreview = null;
      if (!window.matchMedia('(pointer: coarse)').matches) input.focus();
    };

    if (opts.transicion && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      panel.classList.add('saliendo');
      setTimeout(() => {
        panel.classList.remove('saliendo');
        aplicar();
        panel.classList.add('entrando');
        setTimeout(() => panel.classList.remove('entrando'), CONFIG.T_PANEL_ENTRADA);
      }, CONFIG.T_PANEL_SALIDA);
    } else {
      aplicar();
    }
  }

  function bloquearEntrada(bloqueada) {
    $('btn-verificar').disabled = bloqueada;
    $('entrada-respuesta').disabled = bloqueada;
    const bt = $('btn-trayectoria');
    if (bt) bt.disabled = bloqueada;
  }

  function setLanzando(on) {
    const btn = $('btn-verificar');
    const label = $('btn-verificar-label');
    const spin = $('btn-spinner');
    btn.classList.toggle('lanzando', on);
    if (on) {
      label.textContent = 'LANZANDO…';
      spin.classList.remove('oculto');
    } else {
      label.textContent = 'VERIFICAR Y LANZAR';
      spin.classList.add('oculto');
    }
  }

  function marcarEntrada(clase) {
    $('entrada-respuesta').className = clase || '';
  }

  function mostrarEstadoCalculo(mensaje, tipo) {
    const el = $('estado-calculo');
    el.textContent = mensaje;
    el.className = 'estado-calculo ' + (tipo || '');
  }

  function feedback({ tipo, titulo, lineas }) {
    const el = $('feedback');
    el.textContent = '';
    const h = document.createElement('strong');
    h.className = 'feedback-titulo';
    h.textContent = titulo;
    el.appendChild(h);
    (lineas || []).forEach((txt) => {
      const p = document.createElement('span');
      p.className = 'feedback-linea';
      p.textContent = txt;
      el.appendChild(p);
    });
    el.className = 'feedback visible ' + (tipo || '');
  }

  function ocultarFeedback() {
    $('feedback').classList.remove('visible');
  }

  function mostrarResolucion(lineas, inmediato = false) {
    limpiarResolucionTimers();
    const bloque = $('bloque-resolucion');
    const pre = $('resolucion-pasos');
    bloque.classList.remove('oculto');
    pre.textContent = '';
    if (!lineas?.length) return;
    if (inmediato || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      pre.textContent = lineas.join('\n');
      return;
    }
    lineas.forEach((linea, i) => {
      programarUI(i * CONFIG.T_PASO_RESOLUCION, () => {
        pre.textContent += (i ? '\n' : '') + linea;
      });
    });
  }

  function ocultarResolucion() {
    limpiarResolucionTimers();
    $('bloque-resolucion').classList.add('oculto');
    $('resolucion-pasos').textContent = '';
  }

  function mostrarPista() {
    $('btn-pista').classList.remove('oculto');
    $('btn-pista').disabled = false;
  }

  function ocultarPista() {
    $('btn-pista').classList.add('oculto');
    $('btn-pista').disabled = false;
  }

  function shakeHUD(mensaje) {
    const bar = $('barra-superior');
    bar.classList.remove('hud-shake');
    void bar.offsetWidth;
    bar.classList.add('hud-shake');
    if (mensaje) mostrarEstadoCalculo(mensaje, 'error');
  }

  function actualizarProximidadInput(valor) {
    const grupo = $('entrada-grupo');
    grupo.classList.remove('proximidad-verde', 'proximidad-amarilla', 'proximidad-roja');
    if (!Estado.ronda || Estado.fase !== 'calculo') return;
    if (!isFinite(valor)) return;
    const correcta = Estado.ronda.correcta;
    if (!correcta) return;
    const pct = (Math.abs(valor - correcta) / Math.abs(correcta)) * 100;
    if (pct < 5) grupo.classList.add('proximidad-verde');
    else if (pct <= 20) grupo.classList.add('proximidad-amarilla');
    else grupo.classList.add('proximidad-roja');
  }

  function mostrarAscenso(nombreRango) {
    $('ascenso-texto').textContent = `¡ASCENSO A ${nombreRango.toUpperCase()}!`;
    $('overlay-ascenso').classList.remove('oculto');
    setTimeout(() => $('overlay-ascenso').classList.add('oculto'), CONFIG.T_ASCENSO);
  }

  function mostrarPantallaFinal(victoria) {
    ocultarFeedback();
    Estado.fase = 'final';
    Estado.enJuego = false;

    const pts = Math.max(0, Math.round(Estado.puntuacion));
    const metaResult = Meta.finalizarPartida({
      puntuacion: pts,
      rachaMaxSesion: Estado.rachaMaxSesion,
      dominadas: [...Estado.formulasDominadas],
      repasar: [...Estado.formulasRepasar],
    });
    if (metaResult.subioRango) mostrarAscenso(metaResult.rango.nombre);

    const rango = rangoPorPuntos(pts);
    $('final-titulo').textContent = victoria ? '🏆 LYRA SALVADA' : '💔 MISIÓN INCOMPLETA';
    $('final-rango').textContent = `Rango: ${rango.nombre}`;
    $('final-mensaje').textContent = victoria ? Narrativa.finalVictoria(rango.nombre) : Narrativa.finalDerrota();
    $('final-record').textContent = metaResult.nuevoRecord ? `¡NUEVO RÉCORD! ${pts} pts` : `Tu récord: ${metaResult.record} pts`;
    $('final-intentos').textContent = Estado.intentos;
    $('final-puntuacion').textContent = `${pts} / ${CONFIG.PUNTOS_MAX}`;
    $('final-racha').textContent = Estado.rachaMaxSesion;

    const okList = $('final-formulas-ok');
    const repList = $('final-formulas-repaso');
    okList.textContent = '';
    repList.textContent = '';
    const dom = [...Estado.formulasDominadas];
    const rep = [...Estado.formulasRepasar].filter((t) => !Estado.formulasDominadas.has(t));
    $('final-formulas-ok-titulo').textContent = `📖 FÓRMULAS DOMINADAS (${dom.length})`;
    $('final-formulas-repaso-titulo').textContent = `📕 FÓRMULAS PARA REPASAR (${rep.length})`;
    (dom.length ? dom : ['—']).forEach((t) => {
      const li = document.createElement('li');
      li.textContent = dom.length ? `✓ ${t}` : t;
      okList.appendChild(li);
    });
    (rep.length ? rep : ['—']).forEach((t) => {
      const li = document.createElement('li');
      li.textContent = rep.length ? `✕ ${t}` : t;
      repList.appendChild(li);
    });

    $('pantalla-final').classList.remove('oculto');
  }

  function ocultarOverlays() {
    $('pantalla-inicio').classList.add('oculto');
    $('pantalla-final').classList.add('oculto');
    $('overlay-ascenso').classList.add('oculto');
  }

  function mostrarJuego() {
    $('juego').classList.remove('oculto');
  }

  return {
    actualizarHUD,
    aplicarNivel,
    mostrarRonda,
    bloquearEntrada,
    setLanzando,
    marcarEntrada,
    mostrarEstadoCalculo,
    feedback,
    ocultarFeedback,
    mostrarResolucion,
    ocultarResolucion,
    mostrarPista,
    ocultarPista,
    shakeHUD,
    actualizarProximidadInput,
    setNarrativa,
    actualizarAmbiente,
    mostrarPantallaFinal,
    ocultarOverlays,
    mostrarJuego,
  };
})();
