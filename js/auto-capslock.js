document.addEventListener('DOMContentLoaded', function () {
    ['first-name', 'middle-name', 'last-name'].forEach(function (id) {
        var input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function () {
                // Auto Title Case
                this.value = this.value.replace(/\b\w/g, function (char) {
                    return char.toUpperCase();
                }).replace(/\B\w/g, function (char) {
                    return char.toLowerCase();
                });
            });

            input.addEventListener('keydown', function (e) {
                // Detect if Caps Lock is on
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    input.style.textTransform = 'uppercase'; // Visual feedback
                    input.setAttribute('data-capslock', 'on');
                } else {
                    input.style.textTransform = 'none';
                    input.removeAttribute('data-capslock');
                }
            });

            input.addEventListener('blur', function () {
                // Reset styling on blur
                input.style.textTransform = 'none';
                input.removeAttribute('data-capslock');
            });
        }
    });
});
