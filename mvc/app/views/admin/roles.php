<div class="container mt-5">
    <h2 class="mb-4 text-center">Gestión de Roles</h2>
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" class="form-control" id="roleName" placeholder="Nombre del Rol">
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" id="addRoleBtn">+ Agregar Rol</button>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="rolesTableBody">
            <!-- Roles dinámicos -->
        </tbody>
    </table>
</div>
<script>
    document.getElementById('addRoleBtn').addEventListener('click', function() {
        const roleName = document.getElementById('roleName').value;
        if (roleName) {
            // Lógica para agregar rol
            console.log('Rol agregado:', roleName);
        }
    });
</script>