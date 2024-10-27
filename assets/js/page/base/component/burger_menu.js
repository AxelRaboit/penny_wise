const burgerMenu = () => {
    const burgerMenuElement = document.getElementById('js-topbar-burger-menu');
    const spinnerElement = document.querySelector('#topbar-burger-menu-container .spinner');
    if (!burgerMenuElement || !spinnerElement) {
        console.error('burgerMenuElement or spinnerElement is null');
        return;
    }

    spinnerElement.classList.add('hidden');
    burgerMenuElement.classList.remove('hidden');
}

export const initializeBurgerMenu = () => {
    burgerMenu();
}
