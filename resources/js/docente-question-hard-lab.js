const MAX_BLOCKS = 20;

function getElements() {
    return {
        opcionesContainer: document.getElementById('opciones-container'),
        hardLabPanel: document.getElementById('hard-lab-panel'),
        hardBlocksList: document.getElementById('hard-blocks-list'),
        hardBlocksEmpty: document.getElementById('hard-blocks-empty'),
        classicInputs: () => document.querySelectorAll('#opciones-container .input-opcion'),
        classicRadios: () => document.querySelectorAll('#opciones-container .radio-input'),
        hardFields: () => document.querySelectorAll('#hard-lab-panel .hard-field[data-hard-required]'),
    };
}

function renderKatexPreview(textarea) {
    const card = textarea.closest('.hard-block-card');
    const preview = card?.querySelector('.hard-katex-preview');
    if (!preview || typeof katex === 'undefined') {
        return;
    }
    const value = textarea.value.trim();
    if (value === '') {
        preview.textContent = '—';
        return;
    }
    const previewLatex = value.replace(/\[|\]/g, '');
    try {
        katex.render(previewLatex, preview, { throwOnError: false, displayMode: true });
    } catch {
        preview.textContent = 'LaTeX no válido';
    }
}

function bindFormulaPreviews(root) {
    root.querySelectorAll('.hard-latex-input').forEach((textarea) => {
        renderKatexPreview(textarea);
        textarea.addEventListener('input', () => renderKatexPreview(textarea));
    });
}

function bindLatexSnippets(card) {
    card.querySelectorAll('.hard-latex-snippet').forEach((btn) => {
        btn.addEventListener('click', () => {
            const textarea = card.querySelector('.hard-latex-input');
            if (!textarea) {
                return;
            }
            const snippet = btn.getAttribute('data-snippet') || '';
            const start = textarea.selectionStart ?? textarea.value.length;
            const end = textarea.selectionEnd ?? textarea.value.length;
            textarea.value = textarea.value.slice(0, start) + snippet + textarea.value.slice(end);
            textarea.focus();
            renderKatexPreview(textarea);
        });
    });
}

function reindexBlocks() {
    const { hardBlocksList, hardBlocksEmpty } = getElements();
    if (!hardBlocksList) {
        return;
    }

    const cards = hardBlocksList.querySelectorAll('.hard-block-card');
    cards.forEach((card, index) => {
        card.querySelectorAll('[name^="options["]').forEach((input) => {
            const name = input.getAttribute('name');
            if (!name) {
                return;
            }
            input.setAttribute('name', name.replace(/options\[\d+\]/, `options[${index}]`));
        });

        const sortInput = card.querySelector('.hard-field-sort');
        if (sortInput) {
            sortInput.value = String(index);
        }

        const up = card.querySelector('.hard-move-up');
        const down = card.querySelector('.hard-move-down');
        if (up) {
            up.disabled = index === 0;
            up.classList.toggle('opacity-40', index === 0);
        }
        if (down) {
            down.disabled = index === cards.length - 1;
            down.classList.toggle('opacity-40', index === cards.length - 1);
        }
    });

    if (hardBlocksEmpty) {
        hardBlocksEmpty.classList.toggle('hidden', cards.length > 0);
    }

    document.querySelectorAll('[data-add-block]').forEach((btn) => {
        btn.disabled = cards.length >= MAX_BLOCKS;
        btn.classList.toggle('opacity-50', cards.length >= MAX_BLOCKS);
    });
}

function bindBlockCard(card) {
    const removeBtn = card.querySelector('.hard-remove-block');
    const upBtn = card.querySelector('.hard-move-up');
    const downBtn = card.querySelector('.hard-move-down');

    removeBtn?.addEventListener('click', () => {
        const list = card.parentElement;
        if (!list || list.querySelectorAll('.hard-block-card').length <= 1) {
            return;
        }
        card.remove();
        reindexBlocks();
    });

    upBtn?.addEventListener('click', () => {
        const prev = card.previousElementSibling;
        if (prev) {
            card.parentElement?.insertBefore(card, prev);
            reindexBlocks();
        }
    });

    downBtn?.addEventListener('click', () => {
        const next = card.nextElementSibling;
        if (next) {
            card.parentElement?.insertBefore(next, card);
            reindexBlocks();
        }
    });

    bindLatexSnippets(card);
    bindFormulaPreviews(card);
}

function addBlockFromTemplate(type) {
    const { hardBlocksList } = getElements();
    const template = document.getElementById(`hard-block-template-${type}`);
    if (!hardBlocksList || !template || hardBlocksList.querySelectorAll('.hard-block-card').length >= MAX_BLOCKS) {
        return;
    }

    const html = template.innerHTML.replaceAll('__INDEX__', String(hardBlocksList.querySelectorAll('.hard-block-card').length));
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    const card = wrapper.firstElementChild;
    if (!card) {
        return;
    }

    hardBlocksList.appendChild(card);
    bindBlockCard(card);
    reindexBlocks();
}

function setClassicFieldsEnabled(enabled) {
    const { opcionesContainer, classicInputs, classicRadios } = getElements();
    if (!opcionesContainer) {
        return;
    }

    classicInputs().forEach((input) => {
        input.disabled = !enabled;
        if (enabled) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
        }
    });

    classicRadios().forEach((radio) => {
        radio.disabled = !enabled;
        if (!enabled) {
            radio.removeAttribute('required');
        } else {
            radio.setAttribute('required', 'required');
        }
    });
}

function setHardFieldsEnabled(enabled) {
    const { hardLabPanel, hardFields } = getElements();
    if (!hardLabPanel) {
        return;
    }

    hardFields().forEach((input) => {
        input.disabled = !enabled;
        if (enabled) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
        }
    });

    hardLabPanel.querySelectorAll('input.hard-field:not([data-hard-required]), textarea.hard-field:not([data-hard-required])').forEach((input) => {
        input.disabled = !enabled;
    });
}

export function activateHardLaboratoryMode() {
    const { opcionesContainer, hardLabPanel, hardBlocksList } = getElements();
    if (!hardLabPanel || !opcionesContainer) {
        return;
    }

    opcionesContainer.classList.add('hidden');
    hardLabPanel.classList.remove('hidden');
    setClassicFieldsEnabled(false);
    setHardFieldsEnabled(true);

    if (hardBlocksList) {
        hardBlocksList.querySelectorAll('.hard-block-card').forEach(bindBlockCard);
        reindexBlocks();
    }
}

export function deactivateHardLaboratoryMode() {
    const { opcionesContainer, hardLabPanel } = getElements();
    if (!hardLabPanel || !opcionesContainer) {
        return;
    }

    hardLabPanel.classList.add('hidden');
    opcionesContainer.classList.remove('hidden');
    setHardFieldsEnabled(false);
    setClassicFieldsEnabled(true);
}

function initDocenteHardLaboratory() {
    const { hardBlocksList } = getElements();
    if (!hardBlocksList) {
        return;
    }

    hardBlocksList.querySelectorAll('.hard-block-card').forEach(bindBlockCard);
    reindexBlocks();

    document.querySelectorAll('[data-add-block]').forEach((btn) => {
        btn.addEventListener('click', () => {
            addBlockFromTemplate(btn.getAttribute('data-add-block'));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDocenteHardLaboratory();
    window.DocenteHardLab = {
        activate: activateHardLaboratoryMode,
        deactivate: deactivateHardLaboratoryMode,
    };
    document.dispatchEvent(new CustomEvent('docente-hard-lab-ready'));
});
