export function initializeLeftSideMenu({
  openLeftSideMenuButton,
  contentLeftSideMenu,
  closeLeftSideMenuButton,
}) {
  const body = document.body;
  const openButton = document.getElementById(openLeftSideMenuButton);
  const content = document.getElementById(contentLeftSideMenu);
  const closeButton = document.getElementById(closeLeftSideMenuButton);

  if (!openButton || !content) return;

  const openMenu = () => {
    content.classList.remove('-translate-x-full');
    content.classList.add('translate-x-0');
    body.classList.add('overflow-hidden');
    openButton.querySelector('i').classList.replace('fa-bars', 'fa-xmark');
  };

  const closeMenu = () => {
    content.classList.remove('translate-x-0');
    content.classList.add('-translate-x-full');
    body.classList.remove('overflow-hidden');
    openButton.querySelector('i').classList.replace('fa-xmark', 'fa-bars');
  };

  openButton.addEventListener('click', () => {
    const isOpen = !content.classList.contains('-translate-x-full');
    if (isOpen) closeMenu();
    else openMenu();
  });

  closeButton.addEventListener('click', closeMenu);

  document.addEventListener('click', (event) => {
    if (!content.contains(event.target) && !openButton.contains(event.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMenu();
  });
}
