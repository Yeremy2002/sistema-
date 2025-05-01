document.addEventListener("DOMContentLoaded", function () {
    // Función para manejar el logout
    function handleLogout(e) {
        e.preventDefault();
        e.stopPropagation();

        // Crear el formulario
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/logout";

        // Agregar el token CSRF
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        ).content;
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "_token";
        csrfInput.value = csrfToken;

        // Agregar el método _method para simular DELETE
        const methodInput = document.createElement("input");
        methodInput.type = "hidden";
        methodInput.name = "_method";
        methodInput.value = "POST";

        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }

    // Agregar el evento a todos los enlaces que contengan "logout" en su href o texto
    document.querySelectorAll("a").forEach((link) => {
        if (
            link.href.includes("logout") ||
            link.textContent.toLowerCase().includes("cerrar sesión") ||
            link.textContent.toLowerCase().includes("logout")
        ) {
            link.addEventListener("click", handleLogout);
        }
    });
});
