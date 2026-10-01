const MAX_HARD_STEPS = 10;

function getElements() {
    return {
        opcionesContainer: document.getElementById('opciones-container'),
        hardLabPanel: document.getElementById('hard-lab-panel'),
        hardStepsList: document.getElementById('hard-steps-list'),
        hardAddStep: document.getElementById('hard-add-step'),
        hardStepTemplate: document.getElementById('hard-step-template'),
        classicInputs: () => document.querySelectorAll('#opciones-container .input-opcion'),
        classicRadios: () => document.querySelectorAll('#opciones-container .radio-input'),
        hardFields: () => document.querySelectorAll('#hard-lab-panel .hard-field[data-hard-required]'),
    };
}

function reindexHardSteps(listEl) {
    const cards = listEl.querySelectorAll('.hard-step-card');
    cards.forEach((card, index) => {
        card.dataset.stepIndex = String(index);
        const numberEl = card.querySelector('.hard-step-number');
        if (numberEl) {
            numberEl.textContent = `Paso ${index + 1}`;
        }

        const stepLabel = card.querySelector('input[placeholder="Ej: Tiempo de encuentro"]');
        const answer = card.querySelector('input[placeholder="Ej: 12.5 s"]');
        const formula = card.querySelector('input[placeholder="Ej: t = d / v"]');

        if (stepLabel) {
            stepLabel.name = `options[${index}][step_label]`;
        }
        if (answer) {
            answer.name = `options[${index}][option_text]`;
        }
        if (formula) {
            formula.name = `options[${index}][hint_formula]`;
        }

        const removeBtn = card.querySelector('.hard-remove-step');
        if (removeBtn) {
            removeBtn.classList.toggle('hidden', cards.length <= 1);
        }
    });

    const addBtn = document.getElementById('hard-add-step');
    if (addBtn) {
        addBtn.disabled = cards.length >= MAX_HARD_STEPS;
        addBtn.classList.toggle('opacity-50', cards.length >= MAX_HARD_STEPS);
    }
}

function bindRemoveButtons(listEl) {
    listEl.querySelectorAll('.hard-remove-step').forEach((btn) => {
        btn.replaceWith(btn.cloneNode(true));
    });

    listEl.querySelectorAll('.hard-remove-step').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.hard-step-card');
            if (!card || listEl.querySelectorAll('.hard-step-card').length <= 1) {
                return;
            }
            card.remove();
            reindexHardSteps(listEl);
        });
    });
}

function addHardStepFromTemplate(listEl, templateEl) {
    if (!templateEl || listEl.querySelectorAll('.hard-step-card').length >= MAX_HARD_STEPS) {
        return;
    }

    const clone = templateEl.content.firstElementChild.cloneNode(true);
    listEl.appendChild(clone);
    reindexHardSteps(listEl);
    bindRemoveButtons(listEl);
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

    hardLabPanel.querySelectorAll('input.hard-field:not([data-hard-required])').forEach((input) => {
        input.disabled = !enabled;
    });
}

export function activateHardLaboratoryMode() {
    const { opcionesContainer, hardLabPanel, hardStepsList } = getElements();
    if (!hardLabPanel || !opcionesContainer) {
        return;
    }

    opcionesContainer.classList.add('hidden');
    hardLabPanel.classList.remove('hidden');
    setClassicFieldsEnabled(false);
    setHardFieldsEnabled(true);

    if (hardStepsList) {
        bindRemoveButtons(hardStepsList);
        reindexHardSteps(hardStepsList);
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
    const { hardStepsList, hardAddStep, hardStepTemplate } = getElements();
    if (!hardStepsList || !hardAddStep) {
        return;
    }

    bindRemoveButtons(hardStepsList);
    reindexHardSteps(hardStepsList);

    hardAddStep.addEventListener('click', () => {
        addHardStepFromTemplate(hardStepsList, hardStepTemplate);
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
