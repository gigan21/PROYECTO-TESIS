<style>
    @import url('https://fonts.googleapis.com/css2?family=Cabin+Sketch:wght@700&family=Kalam:wght@400;700&display=swap');

    /*
     * Estilos propios de la pizarra. No dependen de que Tailwind detecte clases.
     * IMPORTANTE: no se define display ni opacity en .blackboard-step, .input-mode,
     * .chalk-mode ni #final-submit-container, porque tu JS alterna las clases
     * "hidden" y "opacity-0" sobre ellos.
     */
    #blackboard-form { --wood-1:#5a3520; --wood-2:#3a2113; --wood-3:#26150b; --board-1:#24503c; --board-2:#143024;
        --chalk:#f5f1e3; --gold:#fbbf24; --gold-2:#d97706; --mint:#a7f3d0; }

    /* ---------- Marco de madera + tablero ---------- */
    #blackboard-form .bb-frame { position:relative; border-radius:22px; padding:16px;
        background:
            repeating-linear-gradient(92deg, rgba(0,0,0,.18) 0 2px, transparent 2px 9px),
            linear-gradient(135deg, var(--wood-1), var(--wood-2) 55%, var(--wood-3));
        box-shadow: 0 30px 60px -15px rgba(0,0,0,.75), inset 0 2px 0 rgba(255,255,255,.12), inset 0 -3px 0 rgba(0,0,0,.4); }
    /* clavos dorados en las esquinas */
    #blackboard-form .bb-frame::before, #blackboard-form .bb-frame::after,
    #blackboard-form .bb-nail-b1, #blackboard-form .bb-nail-b2 { content:""; position:absolute; width:11px; height:11px; border-radius:50%;
        background: radial-gradient(circle at 30% 30%, #fde68a, #b45309); box-shadow: 0 1px 2px rgba(0,0,0,.6); }
    #blackboard-form .bb-frame::before { top:3px; left:3px; }
    #blackboard-form .bb-frame::after  { top:3px; right:3px; }
    #blackboard-form .bb-nail-b1 { bottom:3px; left:3px; }
    #blackboard-form .bb-nail-b2 { bottom:3px; right:3px; }

    #blackboard-form .bb-board { position:relative; overflow:hidden; border-radius:10px; padding:2rem 1.5rem 2.5rem;
        background:
            radial-gradient(ellipse at 20% 10%, rgba(255,255,255,.10), transparent 55%),
            radial-gradient(ellipse at 85% 90%, rgba(255,255,255,.06), transparent 50%),
            linear-gradient(160deg, var(--board-1), var(--board-2));
        box-shadow: inset 0 0 70px rgba(0,0,0,.65), inset 0 0 0 2px rgba(0,0,0,.35); }
    @media (min-width:768px){ #blackboard-form .bb-board { padding:2.5rem 3rem 3rem; } }
    /* polvo de tiza */
    #blackboard-form .bb-dust { position:absolute; inset:0; pointer-events:none; opacity:.14; mix-blend-mode:screen;
        background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='220' height='220'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 .9 0'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>"); }
    /* borrones de borrador */
    #blackboard-form .bb-smudge { position:absolute; inset:0; pointer-events:none;
        background:
            radial-gradient(ellipse 260px 70px at 25% 18%, rgba(255,255,255,.05), transparent 70%),
            radial-gradient(ellipse 320px 90px at 75% 62%, rgba(255,255,255,.04), transparent 70%); }

    /* ---------- Título de la pizarra ---------- */
    #blackboard-form .bb-title { position:relative; z-index:1; display:flex; align-items:center; justify-content:center; gap:.75rem; margin-bottom:2rem; }
    #blackboard-form .bb-title span.bb-line { flex:1; max-width:110px; height:2px; border-radius:2px;
        background: linear-gradient(90deg, transparent, rgba(245,241,227,.55)); }
    #blackboard-form .bb-title span.bb-line.r { transform:scaleX(-1); }
    #blackboard-form .bb-title h3 { margin:0; font-family:'Cabin Sketch', cursive; font-size:1.7rem; color:var(--gold);
        text-shadow:0 0 14px rgba(251,191,36,.45), 0 2px 3px rgba(0,0,0,.6); letter-spacing:.02em; text-align:center; }

    /* ---------- Cada paso ---------- */
    #blackboard-form .blackboard-step { padding-bottom:1.75rem; margin-bottom:1.75rem;
        border-bottom:2px dashed rgba(245,241,227,.14); }
    #blackboard-form .blackboard-step:last-child { border-bottom:0; margin-bottom:0; }
    #blackboard-form .bb-head { display:flex; align-items:center; gap:.9rem; margin-bottom:1rem; }
    #blackboard-form .bb-rune { flex:none; width:2.6rem; height:2.6rem; display:grid; place-items:center; border-radius:50%;
        font-family:'Cabin Sketch', cursive; font-size:1.5rem; color:var(--gold);
        border:2px solid rgba(251,191,36,.8); background:rgba(251,191,36,.08);
        box-shadow:0 0 0 4px rgba(251,191,36,.08), 0 0 16px rgba(251,191,36,.35); transform:rotate(-4deg); }
    #blackboard-form .bb-label { margin:0; font-family:'Kalam', cursive; font-weight:700; font-size:1.6rem; line-height:1.2;
        color:var(--chalk); text-shadow:0 0 6px rgba(255,255,255,.25), 0 2px 3px rgba(0,0,0,.5); }
    #blackboard-form .bb-sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }

    /* ---------- Modo escritura: se escribe sobre la pizarra ---------- */
    #blackboard-form .bb-write { flex-wrap:wrap; gap:.9rem; padding-left:3.5rem; }
    @media (max-width:560px){ #blackboard-form .bb-write { padding-left:0; } }
    #blackboard-form .bb-field { position:relative; flex:1 1 260px; max-width:34rem; }
    #blackboard-form .bb-field::before { content:"✎"; position:absolute; left:.2rem; top:50%; transform:translateY(-50%);
        color:rgba(251,191,36,.7); font-size:1.4rem; pointer-events:none; }
    #blackboard-form input.step-input { width:100%; box-sizing:border-box; padding:.55rem .8rem .35rem 2.2rem;
        font-family:'Kalam', cursive; font-size:2rem; line-height:1.3; color:var(--chalk); caret-color:var(--gold);
        text-shadow:0 0 8px rgba(255,255,255,.55);
        background:transparent; border:0; border-bottom:3px dashed rgba(245,241,227,.55); border-radius:0;
        outline:none; box-shadow:none; transition:border-color .2s, background .2s, box-shadow .2s; }
    #blackboard-form input.step-input::placeholder { color:rgba(167,243,208,.35); text-shadow:none; }
    #blackboard-form input.step-input:focus { border-bottom-color:var(--gold);
        background:linear-gradient(180deg, transparent, rgba(251,191,36,.10));
        box-shadow:0 12px 18px -14px rgba(251,191,36,.9); }

    /* Botón OK: sello dorado */
    #blackboard-form .confirm-btn { cursor:pointer; font-family:'Kalam', cursive; font-weight:700; font-size:1.25rem; color:#3a1d05;
        padding:.5rem 1.4rem; border-radius:999px; border:2px solid #fde68a;
        background:linear-gradient(180deg, #fcd34d, var(--gold-2));
        box-shadow:0 4px 0 #92400e, 0 8px 14px rgba(0,0,0,.4); transition:transform .12s, box-shadow .12s, filter .15s; }
    #blackboard-form .confirm-btn:hover { filter:brightness(1.08); transform:translateY(-1px); box-shadow:0 5px 0 #92400e, 0 0 18px rgba(251,191,36,.55); }
    #blackboard-form .confirm-btn:active { transform:translateY(3px); box-shadow:0 1px 0 #92400e; }
    #blackboard-form .confirm-btn:focus-visible, #blackboard-form .bb-done:focus-visible { outline:3px solid var(--mint); outline-offset:3px; }

    /* ---------- Modo tiza escrita ---------- */
    #blackboard-form .bb-done { position:relative; display:inline-flex; align-items:center; gap:1rem; margin-left:3.5rem; padding:.3rem 1.2rem .3rem .4rem;
        border-radius:12px; border:2px dashed rgba(167,243,208,.25); background:rgba(0,0,0,.18); cursor:pointer; transition:background .2s, border-color .2s; }
    @media (max-width:560px){ #blackboard-form .bb-done { margin-left:0; } }
    #blackboard-form .bb-done:hover { background:rgba(0,0,0,.35); border-color:rgba(251,191,36,.6); }
    #blackboard-form .bb-done p { margin:0; font-family:'Kalam', cursive; font-weight:700; font-size:2.3rem; color:var(--chalk);
        text-shadow:0 0 10px rgba(255,255,255,.7), 0 0 26px rgba(167,243,208,.35); }
    #blackboard-form .bb-check { color:#6ee7b7; font-size:1.6rem; text-shadow:0 0 10px rgba(110,231,183,.7); }
    #blackboard-form .bb-erase { font-family:'Kalam', cursive; font-size:.95rem; font-weight:700; color:var(--gold);
        background:rgba(0,0,0,.55); padding:.15rem .7rem; border-radius:999px; opacity:0; transition:opacity .2s; white-space:nowrap; }
    #blackboard-form .bb-done:hover .bb-erase, #blackboard-form .bb-done:focus-visible .bb-erase { opacity:1; }

    /* ---------- Envío final ---------- */
    #blackboard-form .bb-submit { cursor:pointer; font-family:'Cabin Sketch', cursive; font-size:1.9rem; color:#fff; letter-spacing:.02em;
        padding:1rem 2.6rem; border-radius:16px; border:3px solid #6ee7b7;
        background:linear-gradient(180deg, #10b981, #047857); text-shadow:0 2px 3px rgba(0,0,0,.45);
        box-shadow:0 6px 0 #064e3b, 0 0 28px rgba(16,185,129,.6); transition:transform .15s, filter .15s;
        animation:bb-pulse 2.2s ease-in-out infinite; }
    #blackboard-form .bb-submit:hover { transform:translateY(-2px) scale(1.04); filter:brightness(1.1); }
    #blackboard-form .bb-submit:active { transform:translateY(4px); box-shadow:0 2px 0 #064e3b, 0 0 20px rgba(16,185,129,.6); }
    #blackboard-form .bb-submit:focus-visible { outline:3px solid var(--gold); outline-offset:4px; }
    @keyframes bb-pulse { 0%,100% { box-shadow:0 6px 0 #064e3b, 0 0 18px rgba(16,185,129,.45); } 50% { box-shadow:0 6px 0 #064e3b, 0 0 38px rgba(16,185,129,.9); } }

    /* ---------- Repisa con tiza y borrador ---------- */
    #blackboard-form .bb-tray { position:relative; height:16px; margin:-2px 14px 0; border-radius:0 0 8px 8px;
        background:linear-gradient(180deg, #6b4226, var(--wood-2)); box-shadow:0 10px 18px rgba(0,0,0,.5); }
    #blackboard-form .bb-chalk { position:absolute; top:-7px; height:9px; border-radius:4px; box-shadow:0 2px 2px rgba(0,0,0,.4); }
    #blackboard-form .bb-eraser { position:absolute; top:-15px; right:14%; width:64px; height:18px; border-radius:4px;
        background:linear-gradient(180deg, #e5e7eb 0 45%, #6b7280 45%); box-shadow:0 2px 3px rgba(0,0,0,.5); }

    @media (prefers-reduced-motion: reduce){ #blackboard-form .bb-submit { animation:none; } }
</style>

<form method="POST" action="{{ route('estudiante.preguntas.responder', $question) }}" id="blackboard-form" class="mt-8">
    @csrf

    <input type="hidden" name="time_taken" id="time_taken_input" value="0">

    <!-- MARCO DE MADERA -->
    <div class="bb-frame">
        <span class="bb-nail-b1"></span><span class="bb-nail-b2"></span>

        <!-- TABLERO -->
        <div class="bb-board">
            <div class="bb-smudge"></div>
            <div class="bb-dust"></div>

            <div class="bb-title" style="position:relative; z-index:1;">
                <span class="bb-line"></span>
                <h3>Pizarra de Hechizos</h3>
                <span class="bb-line r"></span>
            </div>

            <div class="relative z-10" style="position:relative; z-index:1;" id="blackboard-steps-container">
                @foreach($question->options->sortBy('id')->values() as $index => $option)
                    <div class="blackboard-step hidden opacity-0 transition-all duration-700 ease-in-out" data-index="{{ $index }}">

                        <div class="bb-head">
                            <span class="bb-rune"><span class="bb-sr">Paso </span>{{ $index + 1 }}</span>
                            <p class="bb-label">{{ $option->step_label ?? 'Calcula el valor' }}</p>
                        </div>

                        <!-- MODO 1: ESCRIBIR SOBRE LA PIZARRA -->
                        <div class="input-mode flex items-center gap-4 bb-write">
                            <div class="bb-field">
                                <input type="text"
                                       name="step_answers[{{ $option->id }}]"
                                       class="step-input"
                                       placeholder="Ej: 140 m"
                                       autocomplete="off">
                            </div>
                            <button type="button" class="confirm-btn">OK </button>
                        </div>

                        <!-- MODO 2: TIZA ANIMADA -->
                        <div class="chalk-mode group hidden cursor-pointer">
                            <div class="bb-done" tabindex="0">
                                <span class="bb-check">✔</span>
                                <p><span class="chalk-text"></span></p>
                                <span class="bb-erase"> Clic para corregir</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- BOTÓN DE ENVÍO FINAL -->
            <div class="mt-14 hidden text-center" id="final-submit-container" style="position:relative; z-index:1; margin-top:2.5rem;">
                <button type="submit" class="bb-submit">
                     ¡Comprobar Procedimiento! 
                </button>
            </div>
               
        </div>
    </div>

    <!-- REPISA -->
    <div class="bb-tray" aria-hidden="true">
        <span class="bb-chalk" style="left:9%; width:44px; background:#f5f1e3;"></span>
        <span class="bb-chalk" style="left:9%; margin-left:52px; width:34px; background:#fcd34d;"></span>
        <span class="bb-chalk" style="left:9%; margin-left:94px; width:38px; background:#fda4af;"></span>
        <span class="bb-eraser"></span>
    </div>
</form>

<!-- AHORA SÍ, AFUERA DEL FORMULARIO PRINCIPAL -->
<div class="mt-4 flex justify-center">
    <form action="{{ route('estudiante.preguntas.skip', $question->id) }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <button type="submit" class="w-full rounded-xl border border-white/10 bg-transparent px-6 py-3.5 text-center text-sm font-bold text-slate-400 transition hover:bg-white/5 hover:text-white">
            Saltar misión
        </button>
    </form>
</div>
@vite(['resources/js/blackboard-quiz.js'])