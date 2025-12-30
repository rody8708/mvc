// public/assets/js/invoice_downloads.js

document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".js-download");

    buttons.forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const url = this.dataset.downloadUrl;
            if (!url) return;

            // Abre la descarga en una NUEVA pestaña
            window.open(url, "_blank", "noopener");
        });
    });
});
