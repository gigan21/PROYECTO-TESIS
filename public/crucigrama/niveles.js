const BANCO_PALABRAS = [
  { answer: "MRU", clue: "Movimiento donde la velocidad permanece constante" },
  { answer: "MRUV", clue: "Movimiento con aceleración constante" },
  { answer: "GRAVEDAD", clue: "Aceleración de la atracción terrestre" },
  { answer: "PARABOLA", clue: "Trayectoria de un proyectil" },
];

const NIVEL_MAXIMO = 20;
let avanceEnCurso = false;

let estadoJugador = {
  nivelActual: 1,
  palabrasAprendidas: [],
  coins: 0,
};

let palabrasActuales = [];

function normalizeAnswer(str) {
  return String(str || "").toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, "");
}

function obtenerPalabrasDelNivel(nivel) {
  if (palabrasActuales.length > 0) {
    return palabrasActuales;
  }
  return BANCO_PALABRAS.slice(0, Math.min(nivel, BANCO_PALABRAS.length));
}

function aplicarNivel(nivel) {
  if (nivel < 1 || nivel > NIVEL_MAXIMO) return;
  if (window.descongelarTodas) {
    window.descongelarTodas();
  }
  estadoJugador.nivelActual = nivel;

  const palabrasNivel = obtenerPalabrasDelNivel(nivel);
  window.words.length = 0;
  palabrasNivel.forEach((w, index) => {
    window.words.push({
      answer: normalizeAnswer(w.answer),
      clue: w.clue,
      direction: (nivel + index) % 2 === 0 ? "across" : "down",
    });
  });

  window.generateCrossword();
  window.render();
  actualizarUI();
  guardarProgresoCache();
}

function nivelPalabrasCongeladas() {
  const placed = window.placedWords || [];
  const frozen = window.frozenWords || [];
  if (placed.length === 0) return false;
  return placed.every((w) => frozen.includes(normalizeAnswer(w.answer)));
}

function nivelEstaCompleto() {
  if (window.isWordComplete && window.placedWords) {
    return window.placedWords.every((w) => window.isWordComplete(w));
  }
  return nivelPalabrasCongeladas();
}

async function verificarNivelCompleto() {
  if (avanceEnCurso || !nivelPalabrasCongeladas()) {
    return;
  }
  mostrarMensaje("Nivel completado. Avanzando al siguiente...", "success");
  await new Promise((resolve) => setTimeout(resolve, 1500));
  await completarNivelAutomatico();
}

async function completarNivelAutomatico() {
  if (avanceEnCurso || !nivelEstaCompleto()) {
    return;
  }

  if (estadoJugador.nivelActual >= NIVEL_MAXIMO) {
    mostrarMensaje("Has completado todos los niveles.", "success");
    actualizarUI();
    return;
  }

  avanceEnCurso = true;
  const nuevo = estadoJugador.nivelActual + 1;

  try {
    if (window.CrosswordApi) {
      const res = await window.CrosswordApi.saveProgress("advance_level", { level: nuevo });
      syncFromServer(res.progress);
      const levelData = await window.CrosswordApi.getLevel();
      palabrasActuales = levelData.words || [];
    }
  } catch (e) {
    console.warn("API falló al avanzar nivel:", e);
  }

  aplicarNivel(nuevo);
  actualizarListaAprendidas();
  mostrarMensaje(`Subiste al nivel ${nuevo}.`, "success");
  avanceEnCurso = false;
}

async function registrarPalabraAcertada(respuesta, timeSpent = 0) {
  const answer = normalizeAnswer(respuesta);

  try {
    if (window.CrosswordApi) {
      const res = await window.CrosswordApi.saveProgress("word_learned", {
        word: answer,
        level: estadoJugador.nivelActual,
        time_spent: timeSpent,
      });
      syncFromServer(res.progress);
      if (!estadoJugador.palabrasAprendidas.includes(answer)) {
        estadoJugador.palabrasAprendidas.push(answer);
      }
      if (res.coins_delta > 0) {
        mostrarMensaje(`+${res.coins_delta} moneda(s). Total: ${res.coins}`, "success");
      }
      if (res.xp > 0) {
        mostrarMensaje(`+${res.xp} XP`, "success");
      }
      guardarProgresoCache();
      actualizarListaAprendidas();
      return;
    }
  } catch (e) {
    console.warn("API falló, usando caché local.", e);
  }

  if (!estadoJugador.palabrasAprendidas.includes(answer)) {
    estadoJugador.palabrasAprendidas.push(answer);
  }
  guardarProgresoCache();
  actualizarListaAprendidas();
}

async function registrarIntentoFallido(respuesta) {
  try {
    if (window.CrosswordApi) {
      await window.CrosswordApi.saveProgress("wrong_attempt", {
        word: normalizeAnswer(respuesta),
        level: estadoJugador.nivelActual,
      });
    }
  } catch (e) {
    console.warn(e);
  }
}

function syncFromServer(progress) {
  if (!progress) return;
  estadoJugador.nivelActual = progress.current_level || 1;
  estadoJugador.palabrasAprendidas = (progress.learned_words || []).map(normalizeAnswer);
  estadoJugador.coins = progress.coins_earned || 0;
  const coinsEl = document.getElementById("statCoins");
  if (coinsEl) coinsEl.textContent = `Monedas: ${estadoJugador.coins}`;
  actualizarUI();
}

function actualizarUI() {
  const nivelEl = document.getElementById("nivelActual");
  if (nivelEl) nivelEl.textContent = estadoJugador.nivelActual;
  const maxEl = document.getElementById("nivelMaximo");
  if (maxEl) maxEl.textContent = NIVEL_MAXIMO;
  const btnReiniciar = document.getElementById("btnReiniciarProgreso");
  if (btnReiniciar) {
    btnReiniciar.style.display = estadoJugador.nivelActual >= NIVEL_MAXIMO ? "inline-block" : "none";
  }
}

function actualizarListaAprendidas() {
  const lista = document.getElementById("wordList");
  if (!lista) return;
  lista.innerHTML = "";
  if (estadoJugador.palabrasAprendidas.length === 0) {
    lista.innerHTML = '<li class="empty-msg">Aún no has acertado ninguna palabra.</li>';
    return;
  }
  estadoJugador.palabrasAprendidas.forEach((respuesta) => {
    const palabra = palabrasActuales.find((w) => normalizeAnswer(w.answer) === respuesta)
      || BANCO_PALABRAS.find((w) => normalizeAnswer(w.answer) === respuesta);
    const li = document.createElement("li");
    li.className = "word-item learned";
    li.innerHTML = `<div class="info"><div class="answer">${respuesta}</div><div class="clue-text">${palabra ? palabra.clue : ""}</div></div>`;
    lista.appendChild(li);
  });
}

function mostrarMensaje(texto, tipo = "") {
  const msg = document.getElementById("message");
  if (!msg) return;
  msg.textContent = texto;
  msg.className = `message show ${tipo}`;
  setTimeout(() => msg.classList.remove("show"), 3500);
}

function guardarProgresoCache() {
  localStorage.setItem("crucigrama-estado", JSON.stringify(estadoJugador));
}

function cargarProgresoCache() {
  const guardado = localStorage.getItem("crucigrama-estado");
  if (!guardado) return;
  try {
    estadoJugador = { ...estadoJugador, ...JSON.parse(guardado) };
  } catch (e) {
    console.warn("Caché corrupta.");
  }
}

async function cargarDesdeServidor() {
  if (!window.CrosswordApi) return false;
  try {
    const [progress, levelData] = await Promise.all([
      window.CrosswordApi.getProgress(),
      window.CrosswordApi.getLevel(),
    ]);
    syncFromServer(progress);
    palabrasActuales = levelData.words || [];
    return true;
  } catch (e) {
    console.warn(e);
    return false;
  }
}

async function initNiveles() {
  cargarProgresoCache();
  const ok = await cargarDesdeServidor();

  if (!ok && palabrasActuales.length === 0) {
    palabrasActuales = BANCO_PALABRAS;
  }

  document.getElementById("btnReiniciarProgreso")?.addEventListener("click", async () => {
    if (!confirm("¿Borrar todo tu progreso?")) return;
    try {
      if (window.CrosswordApi) {
        const progress = await window.CrosswordApi.resetProgress();
        syncFromServer(progress);
      }
    } catch (e) {
      console.warn(e);
    }
    localStorage.removeItem("crucigrama-estado");
    estadoJugador = { nivelActual: 1, palabrasAprendidas: [], coins: 0 };
    palabrasActuales = [];
    if (window.CrosswordApi) {
      const levelData = await window.CrosswordApi.getLevel();
      palabrasActuales = levelData.words || [];
    }
    aplicarNivel(1);
    actualizarListaAprendidas();
  });

  aplicarNivel(estadoJugador.nivelActual);
  actualizarListaAprendidas();
}

document.addEventListener("DOMContentLoaded", initNiveles);

window.verificarNivelCompleto = verificarNivelCompleto;
window.registrarPalabraAcertada = registrarPalabraAcertada;
window.registrarIntentoFallido = registrarIntentoFallido;
window.estadoJugador = estadoJugador;
