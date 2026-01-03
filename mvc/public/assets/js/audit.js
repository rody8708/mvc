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

    function updateAuditTable() {
        fetch('/admin/audit/data', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            const auditTableBody = document.getElementById('auditTableBody');
            auditTableBody.innerHTML = '';

            data.logs.forEach(log => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${log.timestamp}</td>
                    <td>${log.user}</td>
                    <td>${log.ip}</td>
                    <td>${log.action}</td>
                    <td>${log.level}</td>
                `;
                auditTableBody.appendChild(row);
            });
        });
    }

    updateAuditTable();
});