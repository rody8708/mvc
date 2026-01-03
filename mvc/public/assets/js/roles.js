// Lógica para la vista de roles
document.addEventListener('DOMContentLoaded', function() {
    const addRoleButton = document.getElementById('addRoleBtn');

    addRoleButton.addEventListener('click', function() {
        const roleName = document.getElementById('roleName').value;

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
                    showFloatingAlert('Role created successfully', 'success');
                    updateRolesTable();
                } else {
                    showFloatingAlert('Error creating role', 'danger');
                }
            });
    });

    function updateRolesTable() {
        fetch('/admin/roles/data', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const rolesTableBody = document.getElementById('rolesTableBody');
                rolesTableBody.innerHTML = '';

                data.roles.forEach(role => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${role.name}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="deleteRole(${role.id})">Delete</button></td>
                `;
                    rolesTableBody.appendChild(row);
                });
            });
    }

    function deleteRole(id) {
        fetch('/admin/roles/delete', {
                method: 'POST',
                body: new URLSearchParams({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFloatingAlert('Role deleted successfully', 'success');
                    updateRolesTable();
                } else {
                    showFloatingAlert('Error deleting role', 'danger');
                }
            });
    }

    updateRolesTable();
});