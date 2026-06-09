document.addEventListener('DOMContentLoaded', function () {
    var showPasswordCheckbox = document.getElementById('show-password');
    if (showPasswordCheckbox) {
        showPasswordCheckbox.addEventListener('change', function () {
            var input = document.getElementById('password');
            if (input) {
                input.type = showPasswordCheckbox.checked ? 'text' : 'password';
            }
        });
    }
});
