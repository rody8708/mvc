// Funciones para la vista de configuración

document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('saveSettingsBtn');

    saveButton.addEventListener('click', function() {
        const siteName = document.getElementById('siteName').value;
        const adminEmail = document.getElementById('adminEmail').value;

        fetch('/admin/settings/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ site_name: siteName, admin_email: adminEmail })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFloatingAlert('Settings updated successfully', 'success');
                } else {
                    showFloatingAlert('Error updating settings', 'danger');
                }
            });
    });
});