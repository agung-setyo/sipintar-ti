function toggleSidebar() {
    document.body.classList.toggle('sidebar-collapsed');
}

document.addEventListener('DOMContentLoaded', function () {
    var toggles = document.querySelectorAll('[data-action="toggle-sidebar"]');
    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    });

    var confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm');
            if (message && !confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
