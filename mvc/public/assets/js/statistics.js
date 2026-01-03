// Lógica para la vista de estadísticas
document.addEventListener('DOMContentLoaded', function() {
    console.log('Estadísticas cargadas correctamente');

    fetch('/admin/statistics/data', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalUsers').textContent = data.total_users;
            document.getElementById('recentLogs').textContent = data.recent_logs;
            document.getElementById('totalNotifications').textContent = data.total_notifications;
        });

    // Aquí puedes agregar lógica adicional para manejar eventos o gráficos dinámicos
});