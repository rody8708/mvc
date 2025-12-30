document.addEventListener("DOMContentLoaded", () => {
    const badge = document.getElementById("notifBadge");
    const notifList = document.getElementById("notificationList");

    function fetchNotifications() {
        fetch(BASE_URL + "notifications/fetch", { method: "POST" })
            .then(res => res.json())
            .then(data => renderNotifications(data));
    }

    function renderNotifications(data) {
        if (!data.success || !notifList || !badge) return;

        notifList.innerHTML = "";

        if (data.notifications.length === 0) {
            notifList.innerHTML = '<li class="dropdown-item text-muted">Sin notificaciones</li>';
            badge.classList.add("d-none");
            return;
        }

        badge.textContent = data.notifications.length;
        badge.classList.remove("d-none");

        data.notifications.forEach(n => {
            const li = document.createElement("li");
            li.className = "dropdown-item text-wrap";
            li.textContent = n.message;
            li.addEventListener("click", () => markAsRead(n.id));
            notifList.appendChild(li);
        });
    }

    function markAsRead(id) {
        fetch(BASE_URL + "notifications/mark-as-read", {
            method: "POST",
            body: new URLSearchParams({ id })
        }).then(() => fetchNotifications());
    }

    // ---------------- POLLING ----------------
    /*if (typeof NOTIFICATION_METHOD !== "undefined" && NOTIFICATION_METHOD === "polling") {
        fetchNotifications();  // al cargar
        setInterval(() => {
            if (!document.hidden) fetchNotifications();
        }, 30000);
    }

    // ---------------- WEBSOCKET ----------------
    if (typeof NOTIFICATION_METHOD !== "undefined" && NOTIFICATION_METHOD === "websocket") {
        const socket = new WebSocket("ws://localhost:8080");

        socket.addEventListener("open", () => {
            console.log("✅ WebSocket conectado");
        });

        socket.addEventListener("message", (event) => {
            const data = JSON.parse(event.data);
            if (data.type === "notification") {
                fetchNotifications();
            }
        });

        socket.addEventListener("close", () => {
            console.warn("WebSocket desconectado");
        });

        socket.addEventListener("error", (error) => {
            console.error("WebSocket error:", error);
        });
    }*/

    fetchNotifications();

    // Al abrir el menú
    const toggle = document.getElementById("notificationToggle");
    if (toggle) toggle.addEventListener("click", fetchNotifications);
});
