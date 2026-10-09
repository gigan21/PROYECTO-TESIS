/* config.js — Constantes y parámetros del juego. */
const CONFIG = {
  ANCHO: 1000,
  ALTO: 560,
  SUELO_Y: 480,

  MEDICO_X: 120,
  PACIENTE_Y: 480,

  LANZ_OFFSET_X: 40,
  LANZ_OFFSET_Y: 5,
  MARGEN_LYRA_DEBAJO_LANZ: 55,

  GRAVEDAD: 9.81,
  ESCALA: 12,

  DOSIS_MAX: 4,
  PUNTOS_MAX: 110,
  PUNTOS_INICIO: 0,
  PUNTOS_POR_NIVEL: [20, 25, 30, 35],
  PENALIZACION_FALLO: 15,
  PENALIZACION_PISTA: 5,
  BONUS_SIN_FALLOS: 20,
  BONUS_PERFECTA: 30,

  TOLERANCIA_BASE: 0.5,
  TOLERANCIA_BASE_S: 0.05,

  RACHA_MULT_2: 2,
  RACHA_MULT_3: 3,
  MULT_RACHA_2: 1.5,
  MULT_RACHA_3: 2,

  RANGOS: [
    { id: 'cadete', nombre: 'Cadete', min: 0, max: 40 },
    { id: 'residente', nombre: 'Residente', min: 41, max: 70 },
    { id: 'cirujano', nombre: 'Cirujano', min: 71, max: 90 },
    { id: 'leyenda', nombre: 'Leyenda', min: 91, max: 110 },
  ],

  RADIO_LYRA_BASE_PX: 22,

  GLOBO_FLOT_AMP: 4,
  GLOBO_FLOT_PERIOD: 2000,
  GLOBO_DRAW_OFFSET_Y: 45,

  T_FEEDBACK_ACIERTO: 2600,
  T_FEEDBACK_FALLO: 3200,
  T_MOVER_PACIENTE: 700,
  T_PANEL_SALIDA: 200,
  T_PANEL_ENTRADA: 250,
  T_PASO_RESOLUCION: 150,
  T_SHAKE: 200,
  T_ASCENSO: 2200,
  T_FONDO_FADE: 280,

  META_STORAGE_KEY: 'lanzaLaCura_meta_v1',

  SPRITE: {
    MEDICO_ALTO: 120,
    PACIENTE_ALTO: 125,
    JERINGA_LADO: 58,
    CORAZON_LADO: 28,
    GLOBO_ALTO: 140,
  },
};

CONFIG.INTENTOS_MAX = CONFIG.DOSIS_MAX;

function rangoPorPuntos(pts) {
  const p = Math.max(0, Math.min(CONFIG.PUNTOS_MAX, Math.round(pts)));
  for (let i = CONFIG.RANGOS.length - 1; i >= 0; i--) {
    const r = CONFIG.RANGOS[i];
    if (p >= r.min) return r;
  }
  return CONFIG.RANGOS[0];
}
