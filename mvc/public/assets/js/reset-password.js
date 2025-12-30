document.getElementById("resetPasswordForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch(BASE_URL + "auth/reset-password", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFloatingAlert(data.message, 'success');
        if (data.success) {
            setTimeout(() => {
                window.location.href = BASE_URL + "auth/login";
            }, 3000);
        }
    })
    .catch(err => {
        showFloatingAlert('Error al procesar la solicitud.', 'danger');   
        console.error(err);
    });
});