import { initializeRightSideMenu } from '@component/side_menu/right_side';

export function initializeAccountListSideMenu() {
  const rightSideMenuConfig = [
    {
      openRightSideMenuButton: 'openAccountListSideMenuTestModal',
      contentRightSideMenu: 'accountListSideMenuTestModal',
      closeRightSideMenuButton: 'closeAccountListSideMenuTestModal',
    },
  ];

  document.addEventListener('turbo:load', () => {
    initializeRightSideMenu(rightSideMenuConfig);
  });
}
