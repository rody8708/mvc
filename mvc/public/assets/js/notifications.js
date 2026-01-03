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

    // Fetch notifications data from the controller
    fetch('/admin/notifications/data', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = '';

            data.notifications.forEach(notification => {
                const item = document.createElement('div');
                item.className = 'notification-card';
                item.innerHTML = `<h5>${notification.message}</h5><p>${notification.level}</p>`;
                notificationList.appendChild(item);
            });
        });

    const createNotificationForm = document.getElementById('createNotificationForm');

    if (createNotificationForm) {
        createNotificationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(createNotificationForm);

            fetch('/admin/notifications/create', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFloatingAlert('Notification created successfully', 'success');
                        updateNotificationsTable();
                    } else {
                        showFloatingAlert('Error creating notification', 'danger');
                    }
                });
        });
    }

    function updateNotificationsTable() {
        fetch('/admin/notifications/data', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const notificationsTableBody = document.getElementById('notificationsTableBody');
                notificationsTableBody.innerHTML = '';

                data.notifications.forEach(notification => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${notification.id}</td>
                    <td>${notification.message}</td>
                    <td>${notification.level}</td>
                    <td>${notification.created_at}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="deleteNotification(${notification.id})">Delete</button></td>
                `;
                    notificationsTableBody.appendChild(row);
                });
            });
    }

    function deleteNotification(id) {
        fetch('/admin/notifications/delete', {
                method: 'POST',
                body: new URLSearchParams({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFloatingAlert('Notification deleted successfully', 'success');
                    updateNotificationsTable();
                } else {
                    showFloatingAlert('Error deleting notification', 'danger');
                }
            });
    }
});