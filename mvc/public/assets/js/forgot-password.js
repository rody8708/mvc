document.getElementById("forgotPasswordForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);    

    fetch(BASE_URL + "auth/send-reset-link", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFloatingAlert(data.message, 'success');
        if (data.success) {
            form.reset();
            // ⏱️ Redirigir si quieres
            setTimeout(() => {
                window.location.href = BASE_URL + "auth/login";
            }, 4000);
        }
    })
    .catch(() => {
        showFloatingAlert('Error al procesar la solicitud', 'danger');            
    });
});