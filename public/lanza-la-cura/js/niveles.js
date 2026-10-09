/* niveles.js — Definición de los 4 niveles y posicionamiento de actores. */
const Niveles = (() => {
  const NIVELES = [
    {
      id: 1,
      nombre: 'Azotea',
      fondo: 'img/fondo.gif',
      sueloY: 480,
      lyraEnGlobo: false,
      lyraLado: 'aleatorio',
      zonaMedico: { xMin: 80, xMax: 160 },
      zonaLyra: { xMin: 600, xMax: 900 },
      alturaLyra: 0,
      obstaculos: [],
      viento: 0,
      gravedad: 1.0,
      tolerancia: 0.10,
      narrativa: 'Lyra está en la azotea. El viento es tranquilo.',
    },
    {
      id: 2,
      nombre: 'Callejón',
      fondo: 'img/Nivel2.gif',
      sueloY: 420,
      lyraEnGlobo: true,
      lyraLado: 'derecha',
      zonaMedico: { xMin: 100, xMax: 200 },
      zonaLyra: { xMin: 600, xMax: 850 },
      alturaLyra: 120,
      obstaculos: [
        { src: 'img/obstaculo1nivel2.png', x: 400, alto: 80, ancho: 90, enAire: false, bloquea: false },
      ],
      viento: 0,
      gravedad: 1.0,
      tolerancia: 0.07,
      narrativa: 'Lyra subió al globo. Mantén la calma y apunta a la cesta.',
    },
    {
      id: 3,
      nombre: 'Puente',
      fondo: 'img/Nivel3.gif',
      sueloY: 440,
      lyraEnGlobo: true,
      lyraLado: 'izquierda',
      zonaMedico: { xMin: 700, xMax: 850 },
      zonaLyra: { xMin: 100, xMax: 350 },
      alturaLyra: 160,
      obstaculos: [
        { src: 'img/obstaculo2Nivel3.png', x: 500, alto: 100, ancho: 95, enAire: false, bloquea: false, reduceZonaAcierto: 0.85 },
      ],
      viento: 1.5,
      gravedad: 1.0,
      tolerancia: 0.05,
      narrativa: '🌪️ Viento lateral. Lyra cruza el puente en globo.',
    },
    {
      id: 4,
      nombre: 'Torre final',
      fondo: 'img/Nivel4.gif',
      sueloY: 460,
      lyraEnGlobo: true,
      lyraLado: 'centro',
      zonaMedico: { xMin: 80, xMax: 180 },
      zonaLyra: { xMin: 500, xMax: 700 },
      alturaLyra: 200,
      obstaculos: [
        {
          src: 'img/obstaculo3Nivel4.gif',
          x: 350,
          alto: 120,
          ancho: 85,
          enAire: true,
          yAire: 180,
          bloquea: true,
        },
      ],
      viento: 0,
      gravedad: 1.4,
      tolerancia: 0.03,
      narrativa: '⚠️ Gravedad aumentada. Lyra flota cerca del castillo.',
    },
  ];

  function rand(min, max) {
    return min + Math.random() * (max - min);
  }

  function getNivel(num) {
    return NIVELES[Math.max(1, Math.min(4, num)) - 1];
  }

  function resolverLado(nivel) {
    const opts = ['izquierda', 'derecha'];
    if (nivel.lyraLado === 'centro') return 'centro';
    if (nivel.lyraLado === 'izquierda' || nivel.lyraLado === 'derecha') return nivel.lyraLado;
    return opts[Math.floor(Math.random() * 2)];
  }

  function xEnZona(z, lado, pos) {
    if (lado === 'centro') return (z.xMin + z.xMax) / 2;
    if (lado === 'izquierda') return rand(z.xMin, z.xMin + (z.xMax - z.xMin) * 0.35);
    if (lado === 'derecha') return rand(z.xMax - (z.xMax - z.xMin) * 0.35, z.xMax);
    return rand(z.xMin, z.xMax);
  }

  function lyraAnchorY(nivel, medicoY) {
    if (!nivel.lyraEnGlobo) return nivel.sueloY;
    const launchY = medicoY + CONFIG.LANZ_OFFSET_Y;
    const visualY = nivel.sueloY - nivel.alturaLyra;
    return Math.max(visualY, launchY + CONFIG.MARGEN_LYRA_DEBAJO_LANZ);
  }

  function posicionarActores(nivel) {
    const lado = resolverLado(nivel);
    let lyraX = xEnZona(nivel.zonaLyra, lado === 'centro' ? 'centro' : lado, 'lyra');
    let medicoX;

    if (lado === 'centro') {
      medicoX = rand(nivel.zonaMedico.xMin, nivel.zonaMedico.xMax);
    } else if (lado === 'izquierda') {
      medicoX = rand(nivel.zonaMedico.xMin, nivel.zonaMedico.xMax);
      if (medicoX <= lyraX) medicoX = Math.min(nivel.zonaMedico.xMax, lyraX + 120);
    } else {
      medicoX = rand(nivel.zonaMedico.xMin, nivel.zonaMedico.xMax);
      if (medicoX >= lyraX) medicoX = Math.max(nivel.zonaMedico.xMin, lyraX - 120);
    }

    const medicoY = nivel.sueloY - CONFIG.SPRITE.MEDICO_ALTO;
    const lyraY = lyraAnchorY(nivel, medicoY);

    return {
      medico: { x: medicoX, y: medicoY },
      lyra: { x: lyraX, y: lyraY, enGlobo: nivel.lyraEnGlobo },
      ladoResuelto: lado,
    };
  }

  function instanciarObstaculos(nivel) {
    return (nivel.obstaculos || []).map((o) => {
      const ancho = o.ancho ?? 80;
      const alto = o.alto ?? 80;
      const y = o.enAire ? o.yAire : nivel.sueloY - alto;
      return {
        ...o,
        ancho,
        alto,
        y,
        rect: { x: o.x - ancho / 2, y, w: ancho, h: alto },
      };
    });
  }

  function factorZonaAcierto(nivel) {
    const obs = nivel.obstaculos || [];
    for (let i = 0; i < obs.length; i++) {
      if (obs[i].reduceZonaAcierto) return obs[i].reduceZonaAcierto;
    }
    return 1;
  }

  return {
    NIVELES,
    getNivel,
    posicionarActores,
    instanciarObstaculos,
    lyraAnchorY,
    factorZonaAcierto,
  };
})();
