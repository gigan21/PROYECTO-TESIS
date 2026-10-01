import Sortable from 'sortablejs';
import confetti from 'canvas-confetti';

// Creamos una función global para poder llamarla solo cuando la respuesta sea correcta
window.lanzarConfeti = function() {
    confetti({
        particleCount: 150,
        spread: 80,
        origin: { y: 0.6 },
        colors: ['#4f46e5', '#10b981', '#f59e0b']
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('sortable-list');
    const form = document.getElementById('puzzle-form');
    const btnSubmit = document.getElementById('btn-submit-puzzle');

    if (list) {
        new Sortable(list, {
            animation: 150,
            ghostClass: 'opacity-50',
            dragClass: 'shadow-2xl',
            handle: '.sortable-item',
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            btnSubmit.disabled = true; 
            btnSubmit.innerHTML = 'Evaluando...';

            const items = list.querySelectorAll('.sortable-item');
            
            items.forEach((item, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `ordered_option_ids[${index}]`;
                input.value = item.getAttribute('data-id');
                form.appendChild(input);
            });

            //  SOLUCIÓN: Enviamos inmediatamente. El servidor decidirá el confeti.
            form.submit();
        });
    }
});