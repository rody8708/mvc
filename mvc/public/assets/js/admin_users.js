function showModalMessage(id, message, type = 'danger') {
    const msg = document.getElementById(id);
    msg.className = `alert alert-${type}`;
    msg.textContent = message;
    msg.classList.remove('d-none');

    setTimeout(() => {
        msg.classList.add('d-none');
        msg.textContent = '';
    }, 4000);
}

// Crear usuario
document.getElementById('addUserForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(BASE_URL + 'admin/create-user', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            form.reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
            const addButton = document.querySelector('[data-bs-target="#addUserModal"]');
            if (addButton) {
                addButton.focus();
            }
            showFloatingAlert(data.message, 'success');

            const name = formData.get('name');
            const email = formData.get('email');
            const is_active = formData.get('is_active') === '1' ? 'Sí' : 'No';
            const role_id = formData.get('role_id');
            const role_name = document.querySelector(`#addUserForm select[name="role_id"] option[value="${role_id}"]`).textContent;

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${name}</td>
                <td>${email}</td>
                <td>${is_active}</td>
                <td>${role_name}</td>
                <td class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning edit-user-btn"
                        data-user='${JSON.stringify({
                            id: data.id,
                            name,
                            email,
                            is_active: parseInt(formData.get('is_active')),
                            role_id: parseInt(role_id),
                            role_name
                        })}'>Editar</button>
                    <button class="btn btn-sm btn-danger delete-user-btn" data-user="${data.id}">Eliminar</button>
                </td>
            `;
            document.querySelector('#usersTable tbody').prepend(newRow);
        } else {
            showModalMessage('addUserMsg', data.message, 'danger');
        }
    })
    .catch(() => showModalMessage('addUserMsg', 'Error al crear usuario.', 'danger'));
});

// Abrir modal de edición
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('edit-user-btn')) {
        const user = JSON.parse(e.target.dataset.user);

        document.getElementById('edit-id').value = user.id;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-password').value = '';

        const roleField = document.getElementById('edit-role');
        if (roleField) {
            roleField.value = user.role_id;
        }

        const activeField = document.getElementById('edit-active');
        if (activeField) {
            activeField.value = parseInt(user.is_active);
        }

        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
});



// Guardar cambios de edición
document.getElementById('editUserForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const userId = formData.get('id');

    fetch(BASE_URL + 'admin/update-user', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            const addButton = document.querySelector('[data-bs-target="#editUserModal"]');
            if (addButton) {
                addButton.focus();
            }
            showFloatingAlert(data.message, 'primary');

            const row = document.querySelector(`.edit-user-btn[data-user*='"id":${userId}']`).closest('tr');
            row.querySelector('td:nth-child(1)').textContent = formData.get('name');
            row.querySelector('td:nth-child(2)').textContent = formData.get('email');

            // 🔥 Traducción del estado
            const statusMap = {
                0: 'Inactiva',
                1: 'Activa',
                2: 'Bloqueada',
                3: 'Cierre solicitado'
            };
            const newStatus = statusMap[parseInt(formData.get('is_active'))] || 'Desconocido';
            row.querySelector('td:nth-child(3)').textContent = newStatus;

            const roleText = document.querySelector(`#editUserForm select[name="role_id"] option[value="${formData.get('role_id')}"]`).textContent;
            row.querySelector('td:nth-child(4)').textContent = roleText;


            const updatedUser = {
                id: userId,
                name: formData.get('name'),
                email: formData.get('email'),
                is_active: parseInt(formData.get('is_active')),
                role_id: parseInt(formData.get('role_id')),
                role_name: roleText
            };
            row.querySelector('.edit-user-btn').dataset.user = JSON.stringify(updatedUser);
        } else {
            showModalMessage('editUserMsg', data.message, 'danger');
        }
    })
    .catch(() => showModalMessage('editUserMsg', 'Error al actualizar usuario.', 'danger'));
});

// Eliminar usuario
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-user-btn')) {
        const userId = e.target.dataset.user;
        if (!confirm('¿Eliminar este usuario?')) return;

        const formData = new FormData();
        formData.append('user_id', userId);

        fetch(BASE_URL + 'admin/delete-user', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showFloatingAlert(data.message, data.success ? 'danger' : 'warning');
            if (data.success) {
                e.target.closest('tr').remove();
            }
        })
        .catch(() => showFloatingAlert('Error al eliminar usuario.', 'danger'));
    }
});

// Filtro dinámico por nombre/correo y rol
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('filterRole').addEventListener('change', filterTable);

function filterTable() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('filterRole').value.toLowerCase();

    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
        const name = row.children[0].textContent.toLowerCase();
        const email = row.children[1].textContent.toLowerCase();
        const role = row.children[3].textContent.toLowerCase();

        const matchesSearch = name.includes(term) || email.includes(term);
        const matchesRole = !roleFilter || role === roleFilter;

        row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
    });
}