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
                    location.reload();
                } else {
                    showFloatingAlert('Error creating role', 'danger');
                }
            });
    });
});