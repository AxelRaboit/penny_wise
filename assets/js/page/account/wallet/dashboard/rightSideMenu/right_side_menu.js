import { initializeRightSideMenu } from '@component/SideMenu/right_side_menu.js';

export function initializeSideMenu() {
    const rightSideMenuConfig = [
        {
            openRightSideMenuButton: 'js-open-right-side-menu-wallet-note-button',
            contentRightSideMenu: 'js-wallet-side-menu-note',
            closeRightSideMenuButton: 'js-right-side-menu-wallet-note-close-button'
        },
        {
            openRightSideMenuButton: 'js-open-right-side-menu-wallet-actions-button',
            contentRightSideMenu: 'js-wallet-side-menu-actions',
            closeRightSideMenuButton: 'js-right-side-menu-wallet-actions-close-button'
        },
        {
            openRightSideMenuButton: 'js-open-right-side-menu-wallet-graphs-button',
            contentRightSideMenu: 'js-wallet-side-menu-graphs',
            closeRightSideMenuButton: 'js-right-side-menu-wallet-graphs-close-button'
        },
        {
            openRightSideMenuButton: 'js-open-right-side-menu-wallet-calendar-button',
            contentRightSideMenu: 'js-wallet-side-menu-calendar',
            closeRightSideMenuButton: 'js-right-side-menu-wallet-calendar-close-button'
        },
        {
            openRightSideMenuButton: 'js-open-right-side-menu-wallet-summary-button',
            contentRightSideMenu: 'js-wallet-side-menu-summary',
            closeRightSideMenuButton: 'js-right-side-menu-wallet-summary-close-button'
        },
    ];

    document.addEventListener('turbo:load', () => {
        initializeRightSideMenu(rightSideMenuConfig);
    });
}
