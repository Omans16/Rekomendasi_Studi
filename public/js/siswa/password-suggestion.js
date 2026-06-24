(function () {
    'use strict';

    const modal = document.getElementById('passwordSuggestionModal');

    if (!modal) {
        return;
    }

    const firstInput = modal.querySelector('input[name="password"]');

    document.body.style.overflow = 'hidden';

    window.addEventListener('load', function () {
        if (firstInput) {
            firstInput.focus();
        }
    });
})();