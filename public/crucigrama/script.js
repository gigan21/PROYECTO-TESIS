/* ============================================================
   DATOS INICIALES DEL CRUCIGRAMA
   Puedes editarlos aquí o usar el panel de edición.
   ============================================================ */
  window.words = [
  { answer: "MRU",            clue: "Movimiento donde la velocidad permanece constante y la aceleración es cero", direction: "across" },
  { answer: "MRUV",           clue: "Movimiento en el que existe aceleración constante", direction: "across" },
  { answer: "GRAVEDAD",       clue: "Magnitud vectorial que mide la aceleración producida por la atracción terrestre", direction: "down" },
  { answer: "PARABOLA",       clue: "Trayectoria descrita por un proyectil bajo la acción de la gravedad", direction: "down" },
  { answer: "FRECUENCIA",     clue: "Número de vueltas o revoluciones completadas en la unidad de tiempo en movimiento circular", direction: "down" },
  { answer: "CENTRIPETA",     clue: "Aceleración dirigida siempre hacia el centro de la trayectoria circular", direction: "down" },
  { answer: "DESPLAZAMIENTO", clue: "Cambio de posición de un cuerpo en un intervalo de tiempo", direction: "across" }
];

/* ============================================================
   MOTOR DEL CRUCIGRAMA
   ============================================================ */
let placedWords = [];
let frozenWords = [];
let grid = [];
let gridRows = 0;
let gridCols = 0;

const GRID_SIZE = 25;

function normalize(str) {
  return str.toUpperCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/Ñ/g, "N")
    .replace(/[^A-Z]/g, "");
}

function createEmptyGrid() {
  grid = Array.from({ length: GRID_SIZE }, () => Array(GRID_SIZE).fill(null));
}

function canPlace(word, row, col, dir, placed) {
  const len = word.length;
  if (dir === "across") {
    if (col + len > GRID_SIZE || row < 0 || row >= GRID_SIZE) return false;
  } else {
    if (row + len > GRID_SIZE || col < 0 || col >= GRID_SIZE) return false;
  }

  let intersections = 0;

  for (let i = 0; i < len; i++) {
    const r = dir === "across" ? row : row + i;
    const c = dir === "across" ? col + i : col;
    const cell = grid[r][c];

    if (cell !== null) {
      if (cell !== word[i]) return false;
      intersections++;
    } else {
      if (dir === "across") {
        if (r > 0 && grid[r-1][c] !== null) return false;
        if (r < GRID_SIZE-1 && grid[r+1][c] !== null) return false;
      } else {
        if (c > 0 && grid[r][c-1] !== null) return false;
        if (c < GRID_SIZE-1 && grid[r][c+1] !== null) return false;
      }
    }
  }

  if (dir === "across") {
    if (col > 0 && grid[row][col-1] !== null) return false;
    if (col + len < GRID_SIZE && grid[row][col+len] !== null) return false;
  } else {
    if (row > 0 && grid[row-1][col] !== null) return false;
    if (row + len < GRID_SIZE && grid[row+len][col] !== null) return false;
  }

  if (placed.length === 0) return true;
  return intersections > 0;
}

function placeWord(word, row, col, dir) {
  const len = word.length;
  for (let i = 0; i < len; i++) {
    const r = dir === "across" ? row : row + i;
    const c = dir === "across" ? col + i : col;
    grid[r][c] = word[i];
  }
}

function removeWordFromGrid(word, row, col, dir) {
  const len = word.length;
  for (let i = 0; i < len; i++) {
    const r = dir === "across" ? row : row + i;
    const c = dir === "across" ? col + i : col;
    grid[r][c] = null;
  }
}

function generateCrossword() {
  createEmptyGrid();
  placedWords.length = 0;
  frozenWords.length = 0;

  const sorted = [...words].sort((a, b) => b.answer.length - a.answer.length);

  const first = sorted[0];
  if (!first) return;

  const startRow = Math.floor(GRID_SIZE / 2);
  const startCol = Math.floor((GRID_SIZE - first.answer.length) / 2);

  placeWord(first.answer, startRow, startCol, first.direction);
  placedWords.push({ ...first, row: startRow, col: startCol });

  for (let i = 1; i < sorted.length; i++) {
    const word = sorted[i];
    let bestPlacement = null;

    for (const placed of placedWords) {
      const placedWord = placed.answer;

      for (let pi = 0; pi < placedWord.length; pi++) {
        for (let wi = 0; wi < word.answer.length; wi++) {
          if (placedWord[pi] !== word.answer[wi]) continue;

          let newRow, newCol, newDir;

          if (placed.direction === "across") {
            newDir = "down";
            newRow = placed.row - wi;
            newCol = placed.col + pi;
          } else {
            newDir = "across";
            newRow = placed.row + pi;
            newCol = placed.col - wi;
          }

          if (canPlace(word.answer, newRow, newCol, newDir, placedWords)) {
            if (!bestPlacement ||
                Math.abs(newRow - GRID_SIZE/2) + Math.abs(newCol - GRID_SIZE/2) <
                Math.abs(bestPlacement.row - GRID_SIZE/2) + Math.abs(bestPlacement.col - GRID_SIZE/2)) {
              bestPlacement = { row: newRow, col: newCol, dir: newDir };
            }
          }
        }
      }
    }

    if (bestPlacement) {
      placeWord(word.answer, bestPlacement.row, bestPlacement.col, bestPlacement.dir);
      placedWords.push({ ...word, direction: bestPlacement.dir, row: bestPlacement.row, col: bestPlacement.col });
    }
  }

  trimGrid();
}

function trimGrid() {
  let minR = GRID_SIZE, maxR = 0, minC = GRID_SIZE, maxC = 0;

  for (let r = 0; r < GRID_SIZE; r++) {
    for (let c = 0; c < GRID_SIZE; c++) {
      if (grid[r][c] !== null) {
        minR = Math.min(minR, r);
        maxR = Math.max(maxR, r);
        minC = Math.min(minC, c);
        maxC = Math.max(maxC, c);
      }
    }
  }

  if (minR > maxR) return;

  gridRows = maxR - minR + 1;
  gridCols = maxC - minC + 1;

  placedWords.forEach(w => {
    w.row -= minR;
    w.col -= minC;
  });

  const newGrid = Array.from({ length: gridRows }, () => Array(gridCols).fill(null));
  for (let r = 0; r < gridRows; r++) {
    for (let c = 0; c < gridCols; c++) {
      newGrid[r][c] = grid[r + minR][c + minC];
    }
  }
  grid = newGrid;
}

/* ============================================================
   NUMERACIÓN DE PISTAS
   ============================================================ */
function assignNumbers() {
  const sorted = [...placedWords].sort((a, b) => a.row - b.row || a.col - b.col);
  const cellNumbers = {};
  let num = 1;

  sorted.forEach(w => {
    const key = `${w.row},${w.col}`;
    if (!cellNumbers[key]) {
      cellNumbers[key] = num++;
    }
    w.number = cellNumbers[key];
  });

  return cellNumbers;
}

/* ============================================================
   RENDERIZADO
   ============================================================ */
let cellNumbers = {};

function render() {
  const crosswordEl = document.getElementById("crossword");
  const acrossEl = document.getElementById("acrossClues");
  const downEl = document.getElementById("downClues");

  const maxWidth = Math.min(window.innerWidth - 80, 700);
  const cellSize = Math.max(24, Math.min(38, Math.floor(maxWidth / gridCols)));

  crosswordEl.style.gridTemplateColumns = `repeat(${gridCols}, ${cellSize}px)`;
  crosswordEl.innerHTML = "";

  cellNumbers = assignNumbers();

  for (let r = 0; r < gridRows; r++) {
    for (let c = 0; c < gridCols; c++) {
      const cell = document.createElement("div");
      const key = `${r},${c}`;

      if (grid[r][c] === null) {
        cell.className = "cell block";
      } else {
        cell.className = "cell";
        cell.dataset.row = r;
        cell.dataset.col = c;
        cell.dataset.answer = grid[r][c];

        const input = document.createElement("input");
        input.type = "text";
        input.maxLength = 1;
        input.dataset.row = r;
        input.dataset.col = c;
        input.addEventListener("input", handleInput);
        input.addEventListener("keydown", handleKeydown);
        input.addEventListener("focus", handleFocus);
        cell.appendChild(input);

        if (cellNumbers[key]) {
          const numEl = document.createElement("span");
          numEl.className = "number";
          numEl.textContent = cellNumbers[key];
          cell.appendChild(numEl);
        }
      }

      crosswordEl.appendChild(cell);
    }
  }

  acrossEl.innerHTML = "";
  downEl.innerHTML = "";

  const acrossWords = placedWords.filter(w => w.direction === "across").sort((a, b) => a.number - b.number);
  const downWords = placedWords.filter(w => w.direction === "down").sort((a, b) => a.number - b.number);

  acrossWords.forEach(w => {
    const li = document.createElement("li");
    li.className = "clue-item";
    li.dataset.answer = w.answer;
    li.dataset.dir = "across";
    li.innerHTML = `<span class="num">${w.number}.</span> ${w.clue} <em>(${w.answer.length})</em>`;
    li.addEventListener("click", () => highlightWord(w));
    acrossEl.appendChild(li);
  });

  downWords.forEach(w => {
    const li = document.createElement("li");
    li.className = "clue-item";
    li.dataset.answer = w.answer;
    li.dataset.dir = "down";
    li.innerHTML = `<span class="num">${w.number}.</span> ${w.clue} <em>(${w.answer.length})</em>`;
    li.addEventListener("click", () => highlightWord(w));
    downEl.appendChild(li);
  });

  updateStats();
  updateWordList();
}

/* ============================================================
   INTERACCIÓN
   ============================================================ */
function handleInput(e) {
  const input = e.target;
  input.value = input.value.toUpperCase().replace(/[^A-ZÑ]/g, "");
  if (input.value) {
    moveToNext(input);
  }
  checkWordComplete(input);
}

function handleKeydown(e) {
  const input = e.target;
  const r = parseInt(input.dataset.row);
  const c = parseInt(input.dataset.col);

  if (e.key === "Backspace" && !input.value) {
    e.preventDefault();
    movePrev(r, c);
  } else if (e.key === "ArrowRight") { e.preventDefault(); focusCell(r, c+1); }
  else if (e.key === "ArrowLeft") { e.preventDefault(); focusCell(r, c-1); }
  else if (e.key === "ArrowDown") { e.preventDefault(); focusCell(r+1, c); }
  else if (e.key === "ArrowUp") { e.preventDefault(); focusCell(r-1, c); }
}

function handleFocus(e) {
  const input = e.target;
  const r = parseInt(input.dataset.row);
  const c = parseInt(input.dataset.col);

  const word = placedWords.find(w => {
    if (w.direction === "across") {
      return w.row === r && c >= w.col && c < w.col + w.answer.length;
    } else {
      return w.col === c && r >= w.row && r < w.row + w.answer.length;
    }
  });

  if (word) {
    document.querySelectorAll(".clue-item").forEach(el => el.classList.remove("active"));
    const clueEl = [...document.querySelectorAll(".clue-item")].find(
      el => el.dataset.answer === word.answer && el.dataset.dir === word.direction
    );
    if (clueEl) clueEl.classList.add("active");
  }
}

function moveToNext(input) {
  const r = parseInt(input.dataset.row);
  const c = parseInt(input.dataset.col);

  const word = placedWords.find(w => {
    if (w.direction === "across") return w.row === r && c >= w.col && c < w.col + w.answer.length;
    else return w.col === c && r >= w.row && r < w.row + w.answer.length;
  });

  if (!word) return;

  let nextR = r, nextC = c;
  if (word.direction === "across") nextC++;
  else nextR++;

  focusCell(nextR, nextC);
}

function movePrev(r, c) {
  const word = placedWords.find(w => {
    if (w.direction === "across") return w.row === r && c > w.col && c <= w.col + w.answer.length;
    else return w.col === c && r > w.row && r <= w.row + w.answer.length;
  });

  if (!word) return;

  let prevR = r, prevC = c;
  if (word.direction === "across") prevC--;
  else prevR--;

  focusCell(prevR, prevC);
}

function focusCell(r, c) {
  const input = document.querySelector(`input[data-row="${r}"][data-col="${c}"]`);
  if (input) input.focus();
}

function highlightWord(word) {
  document.querySelectorAll(".clue-item").forEach(el => el.classList.remove("active"));
  const clueEl = [...document.querySelectorAll(".clue-item")].find(
    el => el.dataset.answer === word.answer && el.dataset.dir === word.direction
  );
  if (clueEl) clueEl.classList.add("active");

  focusCell(word.row, word.col);
}

function isWordComplete(w) {
  for (let i = 0; i < w.answer.length; i++) {
    const rr = w.direction === "across" ? w.row : w.row + i;
    const cc = w.direction === "across" ? w.col + i : w.col;
    const inp = document.querySelector(`input[data-row="${rr}"][data-col="${cc}"]`);
    if (!inp || inp.value.toUpperCase() !== w.answer[i]) {
      return false;
    }
  }
  return true;
}

function freezeWord(w) {
  const key = normalize(w.answer);
  if (frozenWords.includes(key)) {
    return;
  }
  for (let i = 0; i < w.answer.length; i++) {
    const rr = w.direction === "across" ? w.row : w.row + i;
    const cc = w.direction === "across" ? w.col + i : w.col;
    const inp = document.querySelector(`input[data-row="${rr}"][data-col="${cc}"]`);
    const cell = inp?.closest(".cell");
    if (!inp || !cell) continue;
    inp.readOnly = true;
    inp.style.backgroundColor = "#86efac";
    inp.style.cursor = "not-allowed";
    cell.classList.add("correct");
    cell.classList.remove("wrong");
  }
  frozenWords.push(key);
  const clueEl = [...document.querySelectorAll(".clue-item")].find(
    el => el.dataset.answer === w.answer && el.dataset.dir === w.direction
  );
  if (clueEl) clueEl.classList.add("solved");

  if (window.registrarPalabraAcertada) {
    const timeSpent = window._crosswordWordStartMs
      ? Math.round((Date.now() - window._crosswordWordStartMs) / 1000)
      : 0;
    window.registrarPalabraAcertada(w.answer, timeSpent);
  }
  if (window.verificarNivelCompleto) {
    window.verificarNivelCompleto();
  }
}

function descongelarTodas() {
  frozenWords.length = 0;
  document.querySelectorAll(".cell:not(.block) input").forEach((inp) => {
    inp.readOnly = false;
    inp.style.backgroundColor = "";
    inp.style.cursor = "";
  });
}

function checkWordComplete(input) {
  placedWords.forEach(w => {
    const complete = isWordComplete(w);
    const clueEl = [...document.querySelectorAll(".clue-item")].find(
      el => el.dataset.answer === w.answer && el.dataset.dir === w.direction
    );
    if (clueEl && !frozenWords.includes(normalize(w.answer))) {
      clueEl.classList.toggle("solved", complete);
    }
    if (complete) {
      freezeWord(w);
    }
  });

  updateStats();
}

/* ============================================================
   ACCIONES
   ============================================================ */
   /* ============================================================
   COMPROBAR RESPUESTAS
   Marca celdas correctas/incorrectas y muestra el botón
   "Siguiente nivel" SOLO si el 100% está bien.
   ============================================================ */
function checkAnswers() {
  let correctas = 0;
  let incorrectas = 0;
  let vacias = 0;

  // 1) Recorrer cada celda y marcar visualmente
  document.querySelectorAll(".cell:not(.block)").forEach(cell => {
    const input = cell.querySelector("input");
    const answer = cell.dataset.answer;
    cell.classList.remove("correct", "wrong");

    if (!input.value) {
      vacias++;
    } else if (input.value.toUpperCase() === answer) {
      cell.classList.add("correct");
      correctas++;
    } else {
      cell.classList.add("wrong");
      incorrectas++;
    }
  });

  // 2) Verificar si TODAS las palabras están completas y correctas
  const totalPalabras = placedWords.length;
  let palabrasCorrectas = 0;

  placedWords.forEach(w => {
    let completa = true;
    for (let i = 0; i < w.answer.length; i++) {
      const rr = w.direction === "across" ? w.row : w.row + i;
      const cc = w.direction === "across" ? w.col + i : w.col;
      const inp = document.querySelector(`input[data-row="${rr}"][data-col="${cc}"]`);
      if (!inp || inp.value.toUpperCase() !== w.answer[i]) {
        completa = false;
        break;
      }
    }
    if (completa) palabrasCorrectas++;
  });

  placedWords.forEach(w => {
    if (isWordComplete(w)) {
      freezeWord(w);
    }
  });

  const msg = document.getElementById("message");
  if (msg) {
    if (incorrectas === 0 && vacias === 0 && totalPalabras > 0) {
      msg.textContent = "Nivel completado. Avanzando...";
      msg.className = "message show success";
    } else {
      msg.textContent = `Correctas: ${correctas} | Incorrectas: ${incorrectas} | Vacías: ${vacias}`;
      msg.className = "message show";
    }
    setTimeout(() => msg.classList.remove("show"), 4000);
  }

  updateStats();
}

function revealSolutions() {
  document.querySelectorAll(".cell:not(.block)").forEach(cell => {
    const input = cell.querySelector("input");
    input.value = cell.dataset.answer;
    cell.classList.remove("wrong");
    cell.classList.add("correct");
  });
  checkWordComplete(document.querySelector("input"));
}

function resetCrossword() {
  document.querySelectorAll(".cell:not(.block)").forEach(cell => {
    const input = cell.querySelector("input");
    if (!input || input.readOnly) {
      return;
    }
    input.value = "";
    cell.classList.remove("correct", "wrong");
  });
  document.querySelectorAll(".clue-item").forEach(el => {
    const frozen = frozenWords.includes(normalize(el.dataset.answer || ""));
    if (!frozen) {
      el.classList.remove("solved", "active");
    }
  });
  updateStats();
}

function updateStats() {
  const total = placedWords.length;
  let solved = 0;

  placedWords.forEach(w => {
    let complete = true;
    for (let i = 0; i < w.answer.length; i++) {
      const rr = w.direction === "across" ? w.row : w.row + i;
      const cc = w.direction === "across" ? w.col + i : w.col;
      const inp = document.querySelector(`input[data-row="${rr}"][data-col="${cc}"]`);
      if (!inp || inp.value.toUpperCase() !== w.answer[i]) {
        complete = false;
        break;
      }
    }
    if (complete) solved++;
  });

  document.getElementById("statWords").textContent = total;
  document.getElementById("statSolved").textContent = solved;
  document.getElementById("statProgress").textContent = total ? Math.round((solved/total)*100) + "%" : "0%";
}

/* ============================================================
   PANEL DE EDICIÓN
   ============================================================ */
function toggleEditor() {
  document.getElementById("editorPanel").classList.toggle("open");
}

function updateWordList() {
  const list = document.getElementById("wordList");
  list.innerHTML = "";

  if (words.length === 0) {
    list.innerHTML = '<li class="empty-msg">No hay palabras. Agrega una arriba.</li>';
    return;
  }

  words.forEach((w, idx) => {
    const li = document.createElement("li");
    li.className = "word-item";
    li.innerHTML = `
      <div class="info">
        <div class="answer">${w.answer} <span class="dir">${w.direction === "across" ? "H" : "V"}</span></div>
        <div class="clue-text">${w.clue}</div>
      </div>
      <div class="actions">
        <button class="danger" onclick="deleteWord(${idx})">🗑️</button>
      </div>
    `;
    list.appendChild(li);
  });
}

function addWord() {
  const answer = normalize(document.getElementById("inputWord").value);
  const clue = document.getElementById("inputClue").value.trim();
  const direction = document.getElementById("inputDir").value;

  if (!answer || answer.length < 2) {
    alert("La palabra debe tener al menos 2 letras.");
    return;
  }
  if (!clue) {
    alert("Debes escribir una pista.");
    return;
  }

  if (words.some(w => w.answer === answer && w.direction === direction)) {
    alert("Ya existe una palabra igual en esa dirección.");
    return;
  }

  words.push({ answer, clue, direction });
  document.getElementById("inputWord").value = "";
  document.getElementById("inputClue").value = "";

  regenerate();
}

/* ============================================================
   BORRADO CORREGIDO: elimina SOLO la palabra seleccionada
   respetando su dirección (horizontal/vertical).
   ============================================================ */
function deleteWord(idx) {
  if (idx < 0 || idx >= words.length) return;

  const wordToDelete = words[idx];

  if (!confirm(`¿Eliminar "${wordToDelete.answer}" (${wordToDelete.direction === "across" ? "Horizontal" : "Vertical"})?`)) return;

  // 1. Eliminar solo esa palabra del array
  words.splice(idx, 1);

  // 2. Regenerar todo el crucigrama desde cero con las palabras restantes
  regenerate();

  // 3. Mensaje informativo
  const msg = document.getElementById("message");
  msg.textContent = `🗑️ Palabra eliminada: ${wordToDelete.answer}`;
  msg.className = "message show";
  setTimeout(() => msg.classList.remove("show"), 2500);
}

function regenerate() {
  generateCrossword();
  render();
}

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */
window.addEventListener("resize", () => {
  if (placedWords.length) render();
});

window.generateCrossword = generateCrossword;
window.render = render;
window.regenerate = regenerate;
window.updateStats = updateStats;
window.placedWords = placedWords;
window.frozenWords = frozenWords;
window.descongelarTodas = descongelarTodas;
window.isWordComplete = isWordComplete;