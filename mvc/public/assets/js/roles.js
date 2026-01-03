// Lógica para la vista de roles
document.addEventListener('DOMContentLoaded', function() {
    const addRoleButton = document.getElementById('addRoleBtn');

    addRoleButton.addEventListener('click', function() {
        const roleName = document.getElementById('roleName').value;

        if (roleName) {
            fetch('/admin/roles/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ role_name: roleName })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rol creado correctamente');
                        location.reload();
                    } else {
                        alert('Error al crear el rol');
                    }
                });
        } else {
            alert('El nombre del rol es obligatorio');
        }
    });
});