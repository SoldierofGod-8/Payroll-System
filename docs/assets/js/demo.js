(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var loginForm = document.getElementById('demo-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var user = document.getElementById('username').value.trim();
                var pass = document.getElementById('password').value;
                var errorBox = document.getElementById('login-error');

                if (user === 'admin' && pass === 'admin123') {
                    window.location.href = 'dashboard.html';
                } else {
                    if (!errorBox) {
                        errorBox = document.createElement('div');
                        errorBox.className = 'alert alert-error';
                        errorBox.id = 'login-error';
                        loginForm.parentNode.insertBefore(errorBox, loginForm);
                    }
                    errorBox.textContent = 'Invalid username or password. (Demo: admin / admin123)';
                }
            });
        }

        var printButtons = document.querySelectorAll('[data-print]');
        printButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.print();
            });
        });

        var navLinks = document.querySelectorAll('.navbar a');
        navLinks.forEach(function (link) {
            if (link.getAttribute('href') === window.location.pathname.split('/').pop()) {
                link.classList.add('active');
            }
        });
    });
})();