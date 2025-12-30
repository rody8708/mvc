document.addEventListener('DOMContentLoaded', function () {
  // ========== ACTUALIZAR DATOS DEL PERFIL ==========
  const profileForm = document.getElementById('profileForm');
  if (profileForm) {
    profileForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(profileForm);

      fetch(BASE_URL + "profile/update", {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        showFloatingAlert(data.message, data.success ? 'primary' : 'danger');
      })
      .catch(() => {
        showFloatingAlert('Error al actualizar el perfil.', 'danger');
      });
    });
  }

  // ========== CAMBIO DE CONTRASEÑA ==========
  const passwordForm = document.getElementById('passwordForm');
  if (passwordForm) {
    passwordForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(passwordForm);

      fetch(BASE_URL + "profile/change-password", {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          passwordForm.reset();
          if (typeof fetchNotifications === 'function') fetchNotifications();
        }
        showFloatingAlert(data.message, data.success ? 'success' : 'danger');
      })
      .catch(() => {
        showFloatingAlert('Error al cambiar la contraseña.', 'danger');
      });
    });
  }

  // ========== SUBIR AVATAR ==========
  const avatarForm = document.getElementById("avatarForm");
  const avatarInput = document.getElementById("avatarInput");
  const avatarPreview = document.getElementById("avatarPreview");
  const avatarPreviewNav = document.getElementById("avatarPreviewNav");

  if (avatarForm && avatarInput && avatarPreview && avatarPreviewNav) {
    avatarForm.addEventListener("submit", function (e) {
      e.preventDefault(); // evitar reload

      const file = avatarInput.files[0];
      if (!file) {
        showFloatingAlert("Por favor selecciona una imagen.", "warning");
        return;
      }

      const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
      if (!allowedTypes.includes(file.type)) {
        showFloatingAlert("Formato no válido. Usa JPG o PNG.", "danger");
        return;
      }

      if (file.size > 3 * 1024 * 1024) {
        showFloatingAlert("Archivo demasiado grande. Máximo 3MB.", "danger");
        return;
      }

      const formData = new FormData();
      formData.append("avatar", file);

      fetch(BASE_URL + "profile/upload-avatar", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Primero bajar la opacidad
          avatarPreview.style.opacity = "0";
          avatarPreviewNav.style.opacity = "0";

          // Esperar un pequeño momento para recargar imagen
          setTimeout(() => {
            avatarPreview.src = data.avatar + "?t=" + new Date().getTime();
            avatarPreviewNav.src = data.avatar + "?t=" + new Date().getTime();
            
            // Cuando la nueva imagen termine de cargar
            avatarPreview.onload = () => {
              avatarPreview.style.transition = "opacity 0.5s ease"; // Suavizado
              avatarPreview.style.opacity = "1"; // Subir opacidad
              avatarPreviewNav.style.transition = "opacity 0.5s ease"; // Suavizado
              avatarPreviewNav.style.opacity = "1"; // Subir opacidad
            };
          }, 200); // Pequeño delay para efecto más natural

          showFloatingAlert(data.message, "success");
        } else {
          showFloatingAlert(data.message, "danger");
        }
      })

      .catch(() => {
        showFloatingAlert("Error al subir el avatar.", "danger");
      });
    });
  }


  const languageSwitch = document.getElementById('languageSwitch');

  if (languageSwitch) {
    languageSwitch.addEventListener('change', function () {
      const selectedLanguage = this.value;

      fetch(BASE_URL + "profile/change-language", {
        method: "POST",
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ language: selectedLanguage })
      })
      .then(res => res.json())
      .then(data => {
        showFloatingAlert(data.message, data.success ? 'success' : 'danger');
      })
      .catch(() => {
        showFloatingAlert('Error al cambiar idioma.', 'danger');
      });
    });
  }


  const deleteAccountBtn = document.getElementById('deleteAccountBtn');
  
  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener('click', function () {
      if (!confirm('¿Estás seguro de eliminar tu cuenta? Esta acción es irreversible.')) return;

      fetch(BASE_URL + 'profile/delete-account', {
        method: 'POST'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showFloatingAlert(data.message, 'success');
          setTimeout(() => {
            window.location.href = BASE_URL + 'auth/login'; // 🔥 O a tu home
          }, 2000);
        } else {
          showFloatingAlert(data.message, 'danger');
        }
      })
      .catch(() => {
        showFloatingAlert('Error al eliminar cuenta.', 'danger');
      });
    });
  }


});


