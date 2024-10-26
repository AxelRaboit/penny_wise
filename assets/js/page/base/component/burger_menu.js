const burger_menu = () => {
    const burgerMenuElement = document.getElementById('topbar-burger-menu');
    const spinnerElement = document.querySelector('#topbar-burger-menu-container .spinner');
    if (!burgerMenuElement || !spinnerElement) {
        console.error('burgerMenuElement or spinnerElement is null');
        return;
    }

    spinnerElement.classList.add('hidden');
    burgerMenuElement.classList.remove('hidden');
}

export const initializeBurgerMenu = () => {
    burger_menu();
}
