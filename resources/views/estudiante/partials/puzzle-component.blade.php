<!-- CONTENEDOR DEL FONDO ANIMADO (Lluvia) -->
<div class="relative w-full rounded-2xl overflow-hidden shadow-[0_10px_40px_rgba(0,0,0,0.5)] my-8 bg-slate-900" 
     style="background-image: url('{{ asset('images/lluviadefondo.gif') }}'); background-size: cover; background-position: center;">
    
    <!-- Capa oscura semitransparente sobre la lluvia para que el papel resalte -->
    <div class="absolute inset-0 bg-black/50 pointer-events-none z-0"></div>

    <!-- CONTENEDOR DE LA HOJA DE PAPEL (PNG) -->
    <div class="relative z-10 mx-auto max-w-4xl min-h-[600px] p-12 sm:p-24 font-rpg flex flex-col justify-center"
         style="background-image: url('{{ asset('images/papel.png') }}'); background-size: 100% 100%; background-repeat: no-repeat; background-position: center;">
        
        <!-- Título del Mini-Juego -->
        <div class="mb-6 text-center mt-2">
            <h3 class="text-3xl sm:text-4xl font-bold text-black uppercase tracking-widest border-b-4 border-black inline-block pb-2">
                Misión de Orden
            </h3>
            <p class="mt-4 text-xl sm:text-2xl text-black font-semibold leading-relaxed px-4">
                Ordena los pasos cronológicamente para resolver el enigma.
            </p>
        </div>

        <form method="POST" action="{{ route('estudiante.preguntas.responder', $question) }}" id="puzzle-form" class="w-full relative z-20">
            @csrf
            <!-- Input oculto para que Laravel reciba los segundos transcurridos -->
            <input type="hidden" name="time_taken" id="time_taken_input" value="0">
            
            <!-- CONTENEDOR SORTABLE: Bloques negros estilo 8-bits -->
            <div id="sortable-list" class="space-y-4 my-6 max-w-xl mx-auto px-4">
                @foreach ($question->options->shuffle() as $option)
                    <!-- BLOQUES ARRASTRABLES -->
                    <div class="sortable-item group relative flex cursor-grab items-center gap-4 border-4 border-slate-900 bg-slate-900 p-4 text-white shadow-[6px_6px_0px_rgba(0,0,0,0.9)] transition-all hover:translate-y-1 hover:translate-x-1 hover:shadow-[2px_2px_0px_rgba(0,0,0,0.9)] active:cursor-grabbing" data-id="{{ $option->id }}">
                        
                        <!-- Ícono decorativo tipo Pixel Art -->
                        <div class="flex items-center justify-center bg-slate-800 p-2 border-2 border-slate-700 text-slate-400">
                            <span class="text-xl font-bold">::</span>
                        </div>
                        
                        <span class="text-xl sm:text-2xl font-medium tracking-wide leading-tight">{{ $option->option_text }}</span>
                        
                        <!-- Brillo decorativo blanco en la esquina -->
                        <div class="absolute top-1 right-2 w-2 h-2 bg-white opacity-20"></div>
                    </div>
                @endforeach
            </div>

            <!-- BOTONES DE ACCIÓN (Enviar y Saltar) -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 pb-4 px-4">
                
                <button type="submit" id="btn-submit-puzzle" class="w-full sm:w-auto border-4 border-slate-900 bg-emerald-500 px-8 py-3 text-xl font-bold text-slate-900 shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-transform hover:translate-y-1 hover:translate-x-1 hover:shadow-[2px_2px_0px_rgba(0,0,0,0.8)] uppercase">
                    ► Enviar Respuesta
                </button>
                


            </div>
        </form>
        <div class="mt-4 flex justify-center">
            <form action="{{ route('estudiante.preguntas.skip', $question->id) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-slate-400 bg-transparent px-6 py-3 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-200">
                    Saltar misión
                </button>
            </form>
        </div>
    </div>
</div>