(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form[data-confirm]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var message = form.getAttribute('data-confirm');
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });

        var flash = document.querySelector('.alert');
        if (flash) {
            setTimeout(function () {
                flash.style.transition = 'opacity 0.5s';
                flash.style.opacity = '0';
                setTimeout(function () { flash.remove(); }, 550);
            }, 5000);
        }

        var totalCells = document.querySelectorAll('.data-table .total-row .align-right');
        totalCells.forEach(function (cell) {
            cell.style.whiteSpace = 'nowrap';
        });
    });
})();