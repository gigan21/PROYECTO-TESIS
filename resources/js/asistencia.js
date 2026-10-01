document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('[data-attendance-table]');

    if (! table) {
        return;
    }

    const upsertUrl = table.dataset.upsertUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    table.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-attendance-action]');

        if (! button || button.disabled) {
            return;
        }

        const studentId = button.dataset.studentId;
        const status = button.dataset.attendanceAction;
        const row = button.closest('[data-student-row]');

        button.disabled = true;

        try {
            const response = await fetch(upsertUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    student_id: Number(studentId),
                    status,
                }),
            });

            const payload = await response.json().catch(() => ({}));

            if (! response.ok) {
                const message = payload.message || Object.values(payload.errors || {}).flat()[0] || 'No se pudo actualizar la asistencia.';
                throw new Error(message);
            }

            updateRow(row, payload.record);
            updateSummary(payload.resumen);
        } catch (error) {
            alert(error.message);
        } finally {
            button.disabled = false;
        }
    });
});

function updateRow(row, record) {
    if (! row || ! record) {
        return;
    }

    const label = row.querySelector('[data-attendance-label]');
    const time = row.querySelector('[data-attendance-time]');

    if (label) {
        label.textContent = record.status === 'presente' ? 'Presente' : 'Ausente';
        label.className = record.status === 'presente'
            ? 'rounded-full px-2.5 py-1 text-xs font-bold uppercase bg-emerald-100 text-emerald-800'
            : 'rounded-full px-2.5 py-1 text-xs font-bold uppercase bg-rose-100 text-rose-800';
    }

    if (time) {
        time.textContent = record.marked_at;
    }
}

function updateSummary(resumen) {
    if (! resumen) {
        return;
    }

    document.querySelectorAll('[data-resumen]').forEach((node) => {
        const key = node.dataset.resumen;
        if (Object.hasOwn(resumen, key)) {
            node.textContent = resumen[key];
        }
    });
}
