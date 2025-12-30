document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);   

    fetch(BASE_URL + "auth/login", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showFloatingAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = BASE_URL;
            }, 1500);
        } else {
            showFloatingAlert(data.message, 'danger');
        }
    })
    .catch(err => {
        showFloatingAlert('Error inesperado en el login', 'danger');        
        console.error(err);
    });
});