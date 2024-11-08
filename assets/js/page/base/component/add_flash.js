const add_flash = () => {
  const flashMessages = document.querySelectorAll('.alert-flash');
  flashMessages.forEach((flashMessage) => {
    setTimeout(() => {
      flashMessage.classList.add('opacity-0');
      setTimeout(() => {
        flashMessage.classList.add('hidden');
      }, 500);
    }, 5000);
  });
};

export const initializeAddFlash = () => {
  add_flash();
};
