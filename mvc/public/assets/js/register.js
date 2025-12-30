document.getElementById("registerForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Extraer valores para validar
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value;

    // Validaciones
    const errores = [];

    if (!name) {
        errores.push("El nombre es obligatorio.");
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
        errores.push("El correo electrónico no es válido.");
    }

    if (!password || password.length < 6) {
        errores.push("La contraseña debe tener al menos 6 caracteres.");
    }

    if (errores.length > 0) {
        showFloatingAlert(errores, 'danger');
        return;
    }

    // 🔥 Aquí defines tú la ruta del backend
    const url = BASE_URL + "auth/register";

    fetch(url, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showFloatingAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = BASE_URL + "auth/login";
            }, 4000);
        } else {
            showFloatingAlert(data.message, 'danger');
        }
    })
    .catch(err => {
        showFloatingAlert('Error en el registro', 'danger');
        console.error(err);
    });
});