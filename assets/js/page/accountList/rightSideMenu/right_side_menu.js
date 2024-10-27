import { initializeRightSideMenu } from '@component/SideMenu/right_side_menu.js';

export function initializeAccountListSideMenu() {
    const rightSideMenuConfig = [
        {
            openRightSideMenuButton: 'openAccountListSideMenuTestModal',
            contentRightSideMenu: 'accountListSideMenuTestModal',
            closeRightSideMenuButton: 'closeAccountListSideMenuTestModal'
        },
    ];

    document.addEventListener('turbo:load', () => {
        initializeRightSideMenu(rightSideMenuConfig);
    });
}