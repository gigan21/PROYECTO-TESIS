/* canvas-draw.js — Escena: Lyra, globo, obstáculos, zona circular. */
const Lienzo = (() => {
  let ctx = null;
  const obsImg = {};

  const COLOR = {
    cian: '#00f0ff', magenta: '#ff00aa', amarillo: '#f5e600',
    exito: '#00ff88', error: '#ff3355', texto: '#e8f4ff',
  };

  const IMG = {};
  function cargarImagenes() {
    const src = {
      medico: 'img/medicofoto.png',
      paciente: 'img/personaACurar.png',
      globo: 'img/GloboLyraNivel2_3_4.png',
      jeringa: 'img/jeringaparalanzar.png',
      corazonVida: 'img/corazonVida.png',
      corazonPierde: 'img/corazonPierdeVida.png',
    };
    Object.keys(src).forEach((k) => {
      const im = new Image();
      im.src = src[k];
      IMG[k] = im;
    });
  }

  function cargarObstaculo(src) {
    if (obsImg[src]) return obsImg[src];
    const im = new Image();
    im.src = src;
    obsImg[src] = im;
    return im;
  }

  const lista = (im) => !!im && im.complete && im.naturalWidth > 0;

  function init() {
    ctx = document.getElementById('lienzo').getContext('2d');
    cargarImagenes();
  }

  function etiqueta(texto, x, y, color) {
    ctx.save();
    ctx.fillStyle = color;
    ctx.font = 'bold 12px ui-monospace, Consolas, monospace';
    ctx.textAlign = 'center';
    ctx.shadowColor = color;
    ctx.shadowBlur = 8;
    ctx.fillText(texto, x, y);
    ctx.restore();
  }

  function dibujarSuelo() {
    const y = Fisica.sueloY();
    ctx.fillStyle = 'rgba(5, 8, 18, .55)';
    ctx.fillRect(0, y, CONFIG.ANCHO, CONFIG.ALTO - y);
    ctx.save();
    ctx.strokeStyle = COLOR.cian;
    ctx.lineWidth = 2;
    ctx.shadowColor = COLOR.cian;
    ctx.shadowBlur = 10;
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(CONFIG.ANCHO, y);
    ctx.stroke();
    ctx.restore();
  }

  function dibujarZonaLyra() {
    if (!Estado.ronda) return;
    const lyra = Fisica.lyraPosicion();
    const r = Estado.ronda.radioPx ?? CONFIG.RADIO_LYRA_BASE_PX;
    const color = Estado.paciente.herido ? COLOR.exito : COLOR.cian;

    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.shadowColor = color;
    ctx.shadowBlur = 10;
    ctx.beginPath();
    ctx.arc(lyra.x, lyra.y, r, 0, Math.PI * 2);
    ctx.stroke();
    ctx.fillStyle = 'rgba(0,255,136,0.08)';
    ctx.fill();
    ctx.restore();
  }

  function dibujarObstaculos() {
    for (const obs of Estado.obstaculosInstanciados) {
      const im = cargarObstaculo(obs.src);
      const r = obs.rect;
      if (obs.bloquea) {
        ctx.save();
        ctx.fillStyle = 'rgba(255, 51, 85, 0.25)';
        ctx.strokeStyle = 'rgba(255, 51, 85, 0.7)';
        ctx.lineWidth = 2;
        ctx.fillRect(r.x, r.y, r.w, r.h);
        ctx.strokeRect(r.x, r.y, r.w, r.h);
        ctx.restore();
      }
      if (lista(im)) {
        ctx.drawImage(im, r.x, r.y, r.w, r.h);
      } else {
        ctx.fillStyle = 'rgba(120, 120, 140, 0.8)';
        ctx.fillRect(r.x, r.y, r.w, r.h);
      }
    }
  }

  function escalaCorazonAnim(i) {
    const anim = Estado.corazonAnim;
    if (!anim || anim.indice !== i) return 1;
    const t = (Estado.tiempo - anim.t0) / 400;
    if (t >= 1) {
      Estado.corazonAnim = null;
      return 1;
    }
    if (t < 0.35) return 1 + 0.3 * (t / 0.35);
    if (t < 0.65) return 1.3 - 0.5 * ((t - 0.35) / 0.3);
    return 0.8 + 0.2 * ((t - 0.65) / 0.35);
  }

  function dibujarCorazones(lyra) {
    const S = CONFIG.SPRITE.CORAZON_LADO;
    const gap = 6;
    const totalW = CONFIG.DOSIS_MAX * S + (CONFIG.DOSIS_MAX - 1) * gap;
    let cx = lyra.x - totalW / 2 + S / 2;
    const cy = lyra.y - (Estado.lyra.enGlobo ? 95 : CONFIG.SPRITE.PACIENTE_ALTO + 18);

    for (let i = 0; i < CONFIG.DOSIS_MAX; i++) {
      const perdido = Estado.corazonesPerdidos[i];
      const im = perdido ? IMG.corazonPierde : IMG.corazonVida;
      const scale = escalaCorazonAnim(i);
      ctx.save();
      ctx.translate(cx, cy);
      ctx.scale(scale, scale);
      if (lista(im)) ctx.drawImage(im, -S / 2, -S / 2, S, S);
      ctx.restore();
      cx += S + gap;
    }
  }

  function dibujarLyra() {
    const lyra = Fisica.lyraPosicion();
    const herido = Estado.paciente.herido;

    if (Estado.lyra.enGlobo) {
      const im = IMG.globo;
      const h = CONFIG.SPRITE.GLOBO_ALTO;
      const w = lista(im) ? (h * im.naturalWidth) / im.naturalHeight : h * 0.75;
      const drawY = lyra.y - CONFIG.GLOBO_DRAW_OFFSET_Y;
      ctx.save();
      ctx.imageSmoothingEnabled = false;
      ctx.shadowColor = herido ? COLOR.error : COLOR.exito;
      ctx.shadowBlur = herido ? 12 : 16;
      if (lista(im)) ctx.drawImage(im, lyra.x - w / 2, drawY - h, w, h);
      ctx.restore();
    } else {
      const im = IMG.paciente;
      const h = CONFIG.SPRITE.PACIENTE_ALTO;
      const w = lista(im) ? (h * im.naturalWidth) / im.naturalHeight : 60;
      ctx.save();
      ctx.imageSmoothingEnabled = false;
      ctx.shadowColor = herido ? COLOR.error : COLOR.exito;
      ctx.shadowBlur = 14;
      if (lista(im)) ctx.drawImage(im, lyra.x - w / 2, lyra.y - h, w, h);
      ctx.restore();
    }

    dibujarCorazones(lyra);
    etiqueta(herido ? 'LYRA' : '¡LYRA CURADA!', lyra.x, lyra.y + 28, herido ? COLOR.error : COLOR.exito);
  }

  function dibujarMedico() {
    const im = IMG.medico;
    const { x, y } = Estado.medico;
    const h = CONFIG.SPRITE.MEDICO_ALTO;
    const w = lista(im) ? (h * im.naturalWidth) / im.naturalHeight : 60;
    ctx.save();
    ctx.imageSmoothingEnabled = false;
    ctx.translate(x, Fisica.sueloY());
    ctx.scale(-1, 1);
    if (lista(im)) ctx.drawImage(im, -w / 2, -h, w, h);
    ctx.restore();
    etiqueta('MÉDICO', x, Fisica.sueloY() + 24, COLOR.cian);
  }

  function dibujarTrayectoriaPreview() {
    const pts = Estado.trayectoriaPreview;
    if (!pts || pts.length < 2) return;
    ctx.save();
    ctx.strokeStyle = 'rgba(245, 230, 0, 0.85)';
    ctx.lineWidth = 2;
    ctx.setLineDash([6, 6]);
    ctx.beginPath();
    ctx.moveTo(pts[0].x, pts[0].y);
    pts.forEach((p) => ctx.lineTo(p.x, p.y));
    ctx.stroke();
    ctx.restore();
  }

  function dibujarJeringa() {
    const j = Estado.jeringa;
    if (!j.activa && !j.aterrizo) return;
    const im = IMG.jeringa;
    const S = CONFIG.SPRITE.JERINGA_LADO;
    ctx.save();
    ctx.translate(j.x, j.y);
    ctx.rotate(j.angulo + Math.PI / 4);
    if (lista(im)) ctx.drawImage(im, -S / 2, -S / 2, S, S);
    ctx.restore();
  }

  function dibujarParticulas() {
    Estado.particulas.forEach((p) => {
      ctx.save();
      ctx.globalAlpha = p.alpha ?? 1;
      ctx.fillStyle = p.color || COLOR.cian;
      ctx.beginPath();
      ctx.arc(p.x, p.y, 3, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    });
  }

  function dibujarOndaImpacto() {
    const o = Estado.ondaImpacto;
    if (!o) return;
    const k = Math.min(1, (Estado.tiempo - o.t0) / 500);
    if (k >= 1) {
      Estado.ondaImpacto = null;
      return;
    }
    ctx.save();
    ctx.strokeStyle = o.color;
    ctx.globalAlpha = 1 - k * 0.6;
    ctx.beginPath();
    ctx.arc(o.x, o.y, 8 + 26 * k, 0, Math.PI * 2);
    ctx.stroke();
    ctx.restore();
  }

  function dibujarTrayectoria() {
    const j = Estado.jeringa;
    if (j.traza.length > 1) {
      ctx.save();
      ctx.strokeStyle = 'rgba(0, 240, 255, .75)';
      ctx.lineWidth = 2;
      ctx.setLineDash([2, 7]);
      ctx.beginPath();
      ctx.moveTo(j.x0, j.y0);
      j.traza.forEach((p) => ctx.lineTo(p.x, p.y));
      ctx.stroke();
      ctx.restore();
    }
  }

  function render() {
    ctx.save();
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && Estado.shakeCanvasUntil > Estado.tiempo) {
      ctx.translate((Math.random() - 0.5) * 8, (Math.random() - 0.5) * 8);
    }
    ctx.clearRect(0, 0, CONFIG.ANCHO, CONFIG.ALTO);
    dibujarSuelo();
    dibujarObstaculos();
    dibujarZonaLyra();
    dibujarMedico();
    dibujarLyra();
    dibujarTrayectoriaPreview();
    dibujarTrayectoria();
    dibujarParticulas();
    dibujarJeringa();
    dibujarOndaImpacto();
    ctx.restore();
  }

  return { init, render };
})();
