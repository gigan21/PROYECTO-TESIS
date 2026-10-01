document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('blackboard-form');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.blackboard-step'));
    const submitContainer = document.getElementById('final-submit-container');

    if (steps.length === 0) return;

    // Iniciar revelando solo el primer paso
    showStep(0);

    steps.forEach((step, index) => {
        const inputMode = step.querySelector('.input-mode');
        const chalkMode = step.querySelector('.chalk-mode');
        const input = step.querySelector('.step-input');
        const confirmBtn = step.querySelector('.confirm-btn');
        const chalkText = step.querySelector('.chalk-text');

        // Lógica de confirmación de paso individual
        const confirmStep = async () => {
            const val = input.value.trim();
            
            // Validar que no esté vacío
            if (!val) {
                input.classList.add('border-rose-500');
                setTimeout(() => input.classList.remove('border-rose-500'), 400);
                return;
            }

            // Transición a Modo Tiza
            inputMode.classList.add('hidden');
            chalkMode.classList.remove('hidden');
            input.disabled = true;

            // Inyectar animación de escritura
            await typeWriterEffect(chalkText, val);
            input.disabled = false;

            // Revelar el siguiente elemento en cascada
            if (index + 1 < steps.length) {
                showStep(index + 1);
            } else {
                submitContainer.classList.remove('hidden');
            }
        };

        // Escuchar Enter en el input
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Bloquear el submit global prematuro
                confirmStep();
            }
        });

        // Escuchar Clic en OK
        confirmBtn.addEventListener('click', confirmStep);

        // Lógica de Edición: "Borrar tiza" y volver a modo Input
        chalkMode.addEventListener('click', () => {
            chalkMode.classList.add('hidden');
            inputMode.classList.remove('hidden');
            input.focus();
            
            // Ocultar botón final si decide editar algo para forzar la validación de nuevo
            submitContainer.classList.add('hidden');
        });
    });

    function showStep(index) {
        if (!steps[index]) return;
        
        const stepElement = steps[index];
        stepElement.classList.remove('hidden');
        
        // Retraso minúsculo para que CSS procese la eliminación del hidden antes del fade-in
        setTimeout(() => {
            stepElement.classList.remove('opacity-0');
            stepElement.querySelector('.step-input').focus();
        }, 50);
    }

    async function typeWriterEffect(element, text) {
        element.textContent = '';
        for (let i = 0; i < text.length; i++) {
            element.textContent += text.charAt(i);
            // Simular ritmo de escritura humano (entre 50ms y 150ms por letra)
            await new Promise(resolve => setTimeout(resolve, 50 + Math.random() * 100));
        }
    }
});