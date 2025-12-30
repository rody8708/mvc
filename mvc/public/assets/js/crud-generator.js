document.addEventListener('DOMContentLoaded', function () {
    const modulesTableBody = document.querySelector('#modulesTable tbody');
    if (modulesTableBody) {
        loadModules(modulesTableBody);
    } else {
        console.warn('No se encontró #modulesTable en el DOM.');
    }
});


// 🛠️ GENERAR MÓDULO
document.getElementById("crudGeneratorForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(BASE_URL + "admin/generate-module", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFloatingAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            form.reset();
            loadModules(); // ✅ Recargar la tabla automáticamente al crear
        }
    })
    .catch(() => showFloatingAlert('Error al generar módulo', 'danger'));
});


// 📚 TABLA DE MÓDULOS

const modulesTableBody = document.querySelector('#modulesTable tbody');

// Función para cargar los módulos existentes
function loadModules() {
    const modulesTableBody = document.querySelector('#modulesTable tbody');
    if (!modulesTableBody) {
        console.warn('No se encontró #modulesTable en el DOM.');
        return; // 🚫 No hacer nada si no existe
    }

    fetch(BASE_URL + 'admin/get-modules')
    .then(res => {
        if (!res.ok) {
            throw new Error('Error al obtener módulos: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        if (!Array.isArray(data)) {
            throw new Error('La respuesta de módulos no es un array.');
        }

        modulesTableBody.innerHTML = '';

        if (data.length === 0) {
            modulesTableBody.innerHTML = '<tr><td colspan="3" class="text-center">No hay módulos aún.</td></tr>';
            return;
        }

        data.forEach(mod => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${mod.name}</td>
                <td><code>/${mod.slug}</code></td>
                <td>
                    <button class="btn btn-sm btn-danger delete-module-btn" data-module="${mod.slug}">
                        Eliminar
                    </button>
                </td>
            `;
            modulesTableBody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error en loadModules:', error.message);
        showFloatingAlert('Error al cargar los módulos: ' + error.message, 'danger');
    });
}





// Cargar la tabla inicialmente
if (modulesTableBody) {
    loadModules();
}

// Eliminar módulo directamente desde la tabla
modulesTableBody.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-module-btn')) {
        const moduleSlug = e.target.dataset.module;
        if (!confirm(`¿Estás seguro de eliminar el módulo '${moduleSlug}'?`)) return;

        const formData = new FormData();
        formData.append('module', moduleSlug);

        fetch(BASE_URL + "admin/delete-module", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showFloatingAlert(data.message, data.success ? 'warning' : 'danger');
            if (data.success) {
                loadModules(); // ✅ Recargar la tabla tras eliminar
            }
        })
        .catch(() => showFloatingAlert('Error al eliminar módulo', 'danger'));
    }
});
