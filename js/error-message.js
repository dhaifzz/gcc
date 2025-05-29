document.addEventListener('DOMContentLoaded', function() {
           const errorPopup = document.getElementById('errorPopup');
           if (errorPopup) {
               setTimeout(() => {
                   errorPopup.classList.add('show');

        }, 100);

        setTimeout(() => {
                   errorPopup.classList.remove('show');
                   setTimeout(() => {
                       errorPopup.remove();

            }, 300);
        }, 3000);
    }
});