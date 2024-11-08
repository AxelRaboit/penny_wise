export function initializeRightSideMenu(rightSideMenuConfig) {
  const body = document.body;

  rightSideMenuConfig.forEach(
    ({
      openRightSideMenuButton,
      contentRightSideMenu,
      closeRightSideMenuButton,
      additionalClasses = '',
    }) => {
      const openButton = document.getElementById(openRightSideMenuButton);
      const content = document.getElementById(contentRightSideMenu);
      const closeButton = document.getElementById(closeRightSideMenuButton);

      const closeRightSideMenu = () => {
        content.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
      };

      if (openButton && content && closeButton) {
        if (additionalClasses) {
          content.classList.add(...additionalClasses.split(' '));
        }

        openButton.addEventListener('click', () => {
          content.classList.remove('translate-x-full');
          body.classList.add('overflow-hidden');
        });

        closeButton.addEventListener('click', closeRightSideMenu);

        document.addEventListener('click', (event) => {
          if (
            !content.contains(event.target) &&
            !openButton.contains(event.target)
          ) {
            closeRightSideMenu();
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            closeRightSideMenu();
          }
        });

        const buttonsInsideModal = content.querySelectorAll('button');
        buttonsInsideModal.forEach((button) => {
          button.addEventListener('click', (event) => {
            if (
              !button.classList.contains('side-menu-no-close-on-interaction')
            ) {
              closeRightSideMenu();
            }
          });
        });
      }
    },
  );
}
