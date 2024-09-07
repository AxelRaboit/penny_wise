document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('js-menu-toggle');
    const mobileMenu = document.getElementById('js-mobile-menu');

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
});