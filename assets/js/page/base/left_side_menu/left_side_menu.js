import { initializeLeftSideMenu } from "@component/side_menu/left_side_menu.js";

export function initializeLeftSideMenuContent() {
    const leftSideMenuConfig = {
        openLeftSideMenuButton: 'js-topbar-burger-menu',
        contentLeftSideMenu: 'js-left-side-menu',
        closeLeftSideMenuButton: 'js-left-side-menu-close-button'
    };

    document.addEventListener('turbo:load', () => {
        initializeLeftSideMenu(leftSideMenuConfig);
    });
}
