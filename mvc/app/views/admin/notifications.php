<div class="container mt-5">
    <h2 class="mb-4 text-center">Gestión de Notificaciones</h2>

    <!-- Formulario para crear notificaciones -->
    <form id="createNotificationForm" class="mb-4">
        <div class="row g-3">
            <div class="col-md-8">
                <input type="text" name="message" class="form-control" placeholder="Escribe el mensaje de la notificación" required>
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select" required>
                    <option value="INFO">INFO</option>
                    <option value="WARNING">WARNING</option>
                    <option value="ERROR">ERROR</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Crear</button>
            </div>
        </div>
    </form>

    <!-- Tabla de notificaciones -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Mensaje</th>
                    <th>Nivel</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="notificationsTableBody">
                <!-- Aquí se cargarán las notificaciones dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationsTableBody = document.getElementById('notificationsTableBody');

        function loadNotifications() {
            fetch('/admin/notifications/data', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationsTableBody.innerHTML = '';
                    data.notifications.forEach(notification => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${notification.id}</td>
                            <td>${notification[2]}</td>
                            <td>${notification[3]}</td>
                            <td>${notification[5]}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="deleteNotification(${notification.id})">Eliminar</button>
                            </td>
                        `;
                        notificationsTableBody.appendChild(row);
                    });
                } else {
                    console.error('Error al cargar notificaciones:', data.message);
                }
            });
        }

        window.deleteNotification = function(id) {
            fetch('/admin/notifications/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                } else {
                    console.error('Error al eliminar notificación:', data.message);
                }
            });
        }

        loadNotifications();
    });
</script>