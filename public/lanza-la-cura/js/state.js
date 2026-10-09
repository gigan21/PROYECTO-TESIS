/* state.js — Estado global del juego. */
const Estado = {
  enJuego: false,
  intentos: 0,
  fallos: 0,
  dosisRestantes: CONFIG.DOSIS_MAX,
  puntuacion: CONFIG.PUNTOS_INICIO,
  fase: 'inicio',
  nivelActual: 1,
  nivelesCompletados: 0,
  partidaPerfecta: true,
  sesion: 0,

  racha: 0,
  rachaMaxSesion: 0,

  formulaReforzar: null,
  formulasDominadas: new Set(),
  formulasRepasar: new Set(),
  pistaUsadaEnIntento: false,

  corazonesPerdidos: [false, false, false, false],
  corazonAnim: null,

  shakeCanvasUntil: 0,
  particulas: [],
  ondaImpacto: null,
  trayectoriaPreview: null,

  nivelConfig: null,
  obstaculosInstanciados: [],
  sueloY: CONFIG.SUELO_Y,

  medico: { x: CONFIG.MEDICO_X, y: CONFIG.SUELO_Y - 60 },
  lyra: { x: 700, y: CONFIG.SUELO_Y, enGlobo: false },
  paciente: { x: 700, y: CONFIG.PACIENTE_Y, herido: true, animando: false, mov: null },

  jeringa: {
    activa: false,
    x: 0, y: 0,
    x0: 0, y0: 0,
    vx: 0, vy: 0,
    t: 0,
    tVuelo: 0,
    angulo: 0,
    aterrizo: false,
    exitoso: false,
    tAterrizaje: 0,
    traza: [],
    viento: 0,
    choqueObstaculo: false,
  },

  formula: null,
  ronda: null,
  ultimaFormulaId: null,
  ultimoIntento: null,
  ultimosPasos: null,

  tiempo: 0,
  ultimoTimestamp: 0,

  syncPacienteDesdeLyra() {
    this.paciente.x = this.lyra.x;
    this.paciente.y = this.lyra.y;
    this.paciente.herido = true;
  },

  reiniciar() {
    this.enJuego = false;
    this.intentos = 0;
    this.fallos = 0;
    this.dosisRestantes = CONFIG.DOSIS_MAX;
    this.puntuacion = CONFIG.PUNTOS_INICIO;
    this.fase = 'inicio';
    this.nivelActual = 1;
    this.nivelesCompletados = 0;
    this.partidaPerfecta = true;
    this.racha = 0;
    this.rachaMaxSesion = 0;
    this.formulaReforzar = null;
    this.formulasDominadas = new Set();
    this.formulasRepasar = new Set();
    this.pistaUsadaEnIntento = false;
    this.corazonesPerdidos = [false, false, false, false];
    this.corazonAnim = null;
    this.shakeCanvasUntil = 0;
    this.particulas = [];
    this.ondaImpacto = null;
    this.trayectoriaPreview = null;
    this.nivelConfig = null;
    this.obstaculosInstanciados = [];
    this.sueloY = CONFIG.SUELO_Y;
    this.lyra = { x: 700, y: CONFIG.SUELO_Y, enGlobo: false };
    this.medico = { x: CONFIG.MEDICO_X, y: CONFIG.SUELO_Y - 60 };
    this.paciente.mov = null;
    this.syncPacienteDesdeLyra();
    this.jeringa.activa = false;
    this.jeringa.aterrizo = false;
    this.jeringa.exitoso = false;
    this.jeringa.traza = [];
    this.jeringa.choqueObstaculo = false;
    this.formula = null;
    this.ronda = null;
    this.ultimaFormulaId = null;
    this.ultimoIntento = null;
    this.ultimosPasos = null;
  },
};
