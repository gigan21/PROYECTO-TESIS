/* meta.js — Progreso persistente en localStorage. */
const Meta = (() => {
  const defaultData = () => ({
    puntosTotales: 0,
    rangoActual: 'Cadete',
    mejorRacha: 0,
    formulasDominadas: [],
    recordPuntos: 0,
  });

  let data = defaultData();

  function cargar() {
    try {
      const raw = localStorage.getItem(CONFIG.META_STORAGE_KEY);
      if (raw) data = { ...defaultData(), ...JSON.parse(raw) };
    } catch {
      data = defaultData();
    }
    return data;
  }

  function guardar() {
    try {
      localStorage.setItem(CONFIG.META_STORAGE_KEY, JSON.stringify(data));
    } catch { /* ignore */ }
  }

  function get() {
    return data;
  }

  function finalizarPartida({ puntuacion, rachaMaxSesion, dominadas, repasar }) {
    const rango = rangoPorPuntos(puntuacion);
    const rangoPrev = data.rangoActual;
    data.puntosTotales += Math.max(0, puntuacion);
    data.rangoActual = rango.nombre;
    data.mejorRacha = Math.max(data.mejorRacha, rachaMaxSesion);
    const set = new Set(data.formulasDominadas);
    dominadas.forEach((t) => set.add(t));
    repasar.forEach((t) => set.delete(t));
    data.formulasDominadas = [...set];
    const nuevoRecord = puntuacion > data.recordPuntos;
    if (nuevoRecord) data.recordPuntos = puntuacion;
    guardar();
    const subioRango = rango.nombre !== rangoPrev
      && CONFIG.RANGOS.findIndex((r) => r.nombre === rango.nombre)
      > CONFIG.RANGOS.findIndex((r) => r.nombre === rangoPrev);
    return { nuevoRecord, rango, subioRango, record: data.recordPuntos };
  }

  cargar();

  return { cargar, guardar, get, finalizarPartida };
})();
