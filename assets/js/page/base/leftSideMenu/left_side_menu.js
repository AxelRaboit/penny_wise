import {initializeLeftSideMenu} from "@component/SideMenu/left_side_menu.js";

document.addEventListener('DOMContentLoaded', () => {
    initializeLeftSideMenu({
        openLeftSideMenuButton: 'js-topbar-burger-menu',
        contentLeftSideMenu: 'js-left-side-menu',
        closeLeftSideMenuButton: 'js-left-side-menu-close-button',
    });
});
