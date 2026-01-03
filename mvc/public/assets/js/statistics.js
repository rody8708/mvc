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

            const ctx = document.getElementById('statsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.chart_labels,
                    datasets: [{
                        label: 'Actividad del Sistema',
                        data: data.chart_data,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

    // Aquí puedes agregar lógica adicional para manejar eventos o gráficos dinámicos
});