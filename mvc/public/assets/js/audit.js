// Funciones para la vista de auditoría

document.addEventListener('DOMContentLoaded', () => {
    const logEntries = document.querySelectorAll('.audit-log-entry');

    logEntries.forEach(entry => {
        entry.addEventListener('click', () => {
            const logId = entry.dataset.logId;
            // Lógica para mostrar detalles del log
            console.log(`Detalles del log con ID ${logId}`);
        });
    });
});