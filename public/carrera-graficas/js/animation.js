/* animation.js — movimiento del auto en la pista y mini-animación de "Predice". */
const Animation = (() => {
  const $ = (id) => document.getElementById(id);
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const EASE = {
    inOut: (k) => (k < 0.5 ? 2 * k * k : -1 + (4 - 2 * k) * k),
  };

  // Ejecuta fn(k) con k de 0 a 1 durante `ms` milisegundos
  function tween(ms, fn) {
    return new Promise((resolve) => {
      if (reduced) { fn(1); resolve(); return; }
      const t0 = performance.now();
      (function frame(now) {
        const k = Math.min((now - t0) / ms, 1);
        fn(k);
        if (k < 1) requestAnimationFrame(frame); else resolve();
      })(t0);
    });
  }

  // ---------- Auto en la pista (posición 0–100 %) ----------
  let pos = 0;
  function setCar(p) { pos = p; $('car').style.setProperty('--p', p); }
  function resetCar() {
    setCar(0);
    $('car').querySelector('.car-img').style.visibility = 'visible';
    $('car').querySelector('.boom').hidden = true;
  }

  async function moveCar(to, ms = 900) {
    const from = pos;
    await tween(ms, (k) => setCar(from + (to - from) * EASE.inOut(k)));
    setCar(to);
    if (to >= 100) pulse($('car'));
  }

  function restart(el, cls) {
    el.classList.remove(cls);
    void el.offsetWidth; // reinicia la animación CSS
    el.classList.add(cls);
  }
  function stallCar() { restart($('car').querySelector('.car-img'), 'wobble'); }  // respuesta incorrecta: el auto tambalea
  // Todas las rondas perdidas: el auto explota
  // Todas las rondas perdidas: el auto explota
// Todas las rondas perdidas: el auto explota
async function explode() {
  const car = $('car');
  const carImg = car.querySelector('.car-img');
  
  // ✅ Detener cualquier animación de tambaleo
  carImg.classList.remove('wobble');
  await new Promise(r => setTimeout(r, reduced ? 0 : 200)); // deja que se estabilice
  
  carImg.style.visibility = 'hidden';
  const boom = car.querySelector('.boom');
  boom.src = 'img/explosion.gif?' + Date.now(); // reinicia el GIF
  boom.hidden = false;
  
  await new Promise((r) => setTimeout(r, reduced ? 300 : 950));
  boom.hidden = true;
}
  function pulse(el) { restart(el, 'pulse'); }

  // ---------- Mini-animación "Predice" ----------
  // Posición normalizada (0–1) según lo que ocurre con la velocidad
  const DEMO = {
    up:    { pos: (k) => k * k,       label: 'Acelera: cada segundo recorre más distancia.' },
    down:  { pos: (k) => k * (2 - k), label: 'Frena: cada segundo recorre menos distancia hasta detenerse.' },
    const: { pos: (k) => k,           label: 'Velocidad constante: recorre la misma distancia cada segundo.' },
    rest:  { pos: () => 0,            label: 'Permanece detenido.' },
  };

  async function demo(effect) {
    const car = $('demo-car');
    $('demo').hidden = false;
    $('demo-label').textContent = DEMO[effect].label;
    car.classList.toggle('idle', effect === 'rest');
    car.style.setProperty('--d', 0);
    await tween(effect === 'rest' ? 1200 : 2200, (k) => car.style.setProperty('--d', DEMO[effect].pos(k) * 100));
  }
  function hideDemo() { $('demo').hidden = true; }

  return { resetCar, moveCar, stallCar, explode, demo, hideDemo };
})();
