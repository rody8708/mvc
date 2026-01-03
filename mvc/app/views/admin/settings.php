<div class="container mt-5">
    <h2 class="mb-4 text-center">Configuración del Sistema</h2>
    <form id="settingsForm">
        <div class="mb-3">
            <label for="siteName" class="form-label">Nombre del Sitio</label>
            <input type="text" class="form-control" id="siteName" name="site_name" value="{{ site_name }}">
        </div>
        <div class="mb-3">
            <label for="adminEmail" class="form-label">Correo del Administrador</label>
            <input type="email" class="form-control" id="adminEmail" name="admin_email" value="{{ admin_email }}">
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>
<script>
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Lógica para guardar configuración
        console.log('Configuración guardada');
    });
</script>