<div class="container mt-5">
    <h2 class="mb-4 text-center">Estadísticas del Sistema</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Usuarios Registrados</h5>
                    <p class="card-text display-4">{{ total_users }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Logs Recientes</h5>
                    <p class="card-text display-4">{{ recent_logs }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Notificaciones Enviadas</h5>
                    <p class="card-text display-4">{{ total_notifications }}</p>
                </div>
            </div>
        </div>
    </div>
    <canvas id="statsChart" class="mt-5"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/admin/statistics/data', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.querySelector('.card-text.display-4:nth-child(1)').textContent = data.total_users;
                document.querySelector('.card-text.display-4:nth-child(2)').textContent = data.recent_logs;
                document.querySelector('.card-text.display-4:nth-child(3)').textContent = data.total_notifications;

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
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                console.error('No se pudieron cargar los datos de estadísticas.');
            }
        });
    });
</script>