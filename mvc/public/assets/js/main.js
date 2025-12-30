// ALERTAS FOLOTANTES
function showFloatingAlert(message, type = 'success') {
    const alertBox = document.getElementById('floatingAlert');
    if (!alertBox) return;

    alertBox.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3 text-center`;
    alertBox.innerHTML = message;
    alertBox.classList.remove('d-none');

    setTimeout(() => {
        alertBox.classList.add('d-none');
        alertBox.innerHTML = '';
    }, 4000);
}

// MODO OSCURO
document.addEventListener("DOMContentLoaded", function () {
  const toggleSwitch = document.getElementById("toggleDarkModeSwitch");
  const toggleButton = document.getElementById("toggleDarkModeBtn");
  const darkModeLoader = document.getElementById("darkModeLoader");

  function setDarkMode(isDark) {
    if (isDark) {
      document.documentElement.classList.add("dark-mode");
      document.body.classList.add("dark-mode");
    } else {
      document.documentElement.classList.remove("dark-mode");
      document.body.classList.remove("dark-mode");
    }

    try {
      localStorage.setItem("darkMode", isDark ? "true" : "false");
    } catch (e) {}

    if (darkModeLoader) {
      darkModeLoader.classList.remove("d-none");
    }

    fetch(BASE_URL + "profile/toggle-dark-mode", {
      method: "POST",
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({ dark_mode: isDark ? 1 : 0 })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        console.error("Error al actualizar modo oscuro:", data.message);
      }
    })
    .catch(err => {
      console.error("Error de conexión:", err);
    })
    .finally(() => {
      if (darkModeLoader) {
        darkModeLoader.classList.add("d-none");
      }
    });
  }

  if (toggleSwitch) {
    toggleSwitch.addEventListener("change", function () {
      setDarkMode(toggleSwitch.checked);
    });
  }

  document.addEventListener("click", function (e) {
    if (e.target && e.target.id === "toggleDarkModeBtn") {
      const isDark = !document.body.classList.contains("dark-mode");
      setDarkMode(isDark);

      // Si existe el switch, sincronizarlo visualmente
      if (toggleSwitch) {
        toggleSwitch.checked = isDark;
      }

      //console.log("🌙 Botón navbar activado:", isDark ? "Oscuro" : "Claro");
    }
  });
});







//// EXPANDIR TEXT
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("text-limit")) {
        e.target.classList.toggle("text-full");
    }
});


document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (!trigger || !menu) return;

        // Quitamos el control nativo de Bootstrap
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const isVisible = menu.classList.contains('showing');

            // Cerrar todos los menús abiertos
            document.querySelectorAll('.dropdown-menu.showing').forEach(openMenu => {
                openMenu.classList.remove('showing');
            });

            if (!isVisible) {
                menu.classList.add('showing');
            }
        });
    });

    // Cerrar dropdown si haces clic fuera
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.showing').forEach(menu => {
            menu.classList.remove('showing');
        });
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const badge = document.getElementById("notifBadge");
    const notifList = document.getElementById("notificationList");

    window.fetchNotifications = function () {
        fetch(BASE_URL + "notifications/fetch", { method: "POST" })
            .then(res => res.json())
            .then(data => renderNotifications(data));
    };


    function renderNotifications(data) {
        if (!data.success) return;

        const notifList = document.getElementById("notificationList");
        const badge = document.getElementById("notifBadge");
        const markAllBtn = document.getElementById("markAllContainer");

        notifList.innerHTML = "";

        if (data.notifications.length === 0) {
            notifList.innerHTML = '<div class="dropdown-item text-wrap text-center">Sin notificaciones</div>';
            badge.classList.add("d-none");
            markAllBtn.classList.add("d-none");
            return;
        }

        badge.textContent = data.notifications.length;
        badge.classList.remove("d-none");

        data.notifications.forEach(n => {
            const div = document.createElement("div");
            div.className = "dropdown-item text-wrap";
            div.textContent = n.message;
            div.addEventListener("click", () => markAsRead(n.id));
            notifList.appendChild(div);
        });

        // Mostrar el botón si hay más de 10
        if (data.notifications.length > 0) {
            markAllBtn.classList.remove("d-none");
        } else {
            markAllBtn.classList.add("d-none");
        }
    }

    // Acción para marcar todas
    const markAllBtn = document.getElementById("markAllBtn");
    if (markAllBtn) {
        markAllBtn.addEventListener("click", () => {
            fetch(BASE_URL + "notifications/mark-all-read", { method: "POST" })
                .then(() => fetchNotifications());
        });
    }




    function markAsRead(id) {
        fetch(BASE_URL + "notifications/mark-as-read", {
            method: "POST",
            body: new URLSearchParams({ id })
        }).then(() => fetchNotifications());
    }

    function markAllAsRead() {
        fetch(BASE_URL + "notifications/mark-all-read", {
            method: "POST"
        }).then(() => fetchNotifications());
    }

    fetchNotifications();

    // Al abrir el menú
    const toggle = document.getElementById("notificationToggle");
    if (toggle) toggle.addEventListener("click", fetchNotifications);
});


// 🔄 Cerrar cualquier dropdown abierto si se abre otro
document.addEventListener("click", function (e) {
  const allDropdowns = document.querySelectorAll('.dropdown-menu.show');
  if (!e.target.closest('.dropdown')) {
    allDropdowns.forEach(d => d.classList.remove('show'));
  }
});


document.addEventListener("DOMContentLoaded", () => {
  // Cierra dropdowns abiertos al abrir otro
  document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
      document.querySelectorAll('.dropdown-menu.show').forEach(open => {
        if (!this.nextElementSibling.isSameNode(open)) {
          open.classList.remove('show');
        }
      });
    });
  });

  // Cierra el menú offcanvas al hacer clic en cualquier enlace
  document.querySelectorAll('.offcanvas a.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sideMenu'));
      if (offcanvas) offcanvas.hide();
    });
  });
});


function renderChart(id, type, data, options = {}) {
  const ctx = document.getElementById(id).getContext('2d');

  // Destruir gráfico anterior si ya existe
  if (window[id]) {
    window[id].destroy();
  }

  window[id] = new Chart(ctx, {
    type: type, // 'bar', 'line', 'pie', etc.
    data: data,
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: getComputedStyle(document.body).color } },
        tooltip: {
          backgroundColor: getComputedStyle(document.body).backgroundColor,
          titleColor: getComputedStyle(document.body).color,
          bodyColor: getComputedStyle(document.body).color
        }
      },
      scales: {
        x: { ticks: { color: getComputedStyle(document.body).color } },
        y: { ticks: { color: getComputedStyle(document.body).color } }
      }
    }, options)
  });
}


document.addEventListener("DOMContentLoaded", () => {
  const main = document.querySelector("main");

  if (main && !document.querySelector(".modal.show")) {
    main.classList.add("fade-transition");
    setTimeout(() => main.classList.add("show"), 10);
  }

  document.querySelectorAll("a[href]").forEach(link => {
    const href = link.getAttribute("href");
    if (href && href.startsWith(BASE_URL)) {
      link.addEventListener("click", function (e) {
        // Si hay un modal abierto, no animamos
        if (document.querySelector(".modal.show")) return;

        e.preventDefault();
        main.classList.remove("show");
        main.classList.add("fade-out");
        setTimeout(() => window.location.href = href, 300);
      });
    }
  });

  // ✅ Mostrar animación de entrada al cargar
  if (main) {
    main.classList.add("fade-transition");
    setTimeout(() => main.classList.add("show"), 10);
  }

  // ✅ Interceptar enlaces para animación de salida
  document.querySelectorAll("a[href]").forEach(link => {
    const href = link.getAttribute("href");

    // Solo aplica para navegación interna
    if (href && href.startsWith(BASE_URL)) {
      link.addEventListener("click", function (e) {
        // Evitar navegación automática
        e.preventDefault();

        // Aplicar clase de salida
        if (main) {
          main.classList.remove("show");
          main.classList.add("fade-out");

          // Esperar la animación antes de ir a la nueva página
          setTimeout(() => {
            window.location.href = href;
          }, 300); // Mismo tiempo que la animación
        } else {
          window.location.href = href; // Fallback
        }
      });
    }
  });
});


