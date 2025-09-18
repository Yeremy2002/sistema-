document.addEventListener("DOMContentLoaded", function () {
    const logoutButtons = document.querySelectorAll(".btn-logout");

    logoutButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/logout";

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            ).content;
            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = csrfToken;

            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        });
    });
});
