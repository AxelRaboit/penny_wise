export function initializeModals(modalConfig) {
    const body = document.body;

    modalConfig.forEach(({ buttonId, modalId, closeId, additionalClasses = '' }) => {
        const button = document.getElementById(buttonId);
        const modal = document.getElementById(modalId);
        const closeModalButton = document.getElementById(closeId);

        const closeModal = () => {
            modal.classList.add('translate-x-full');
            body.classList.remove('overflow-hidden');
        };

        if (button && modal && closeModalButton) {
            if (additionalClasses) {
                modal.classList.add(...additionalClasses.split(' '));
            }

            button.addEventListener('click', () => {
                modal.classList.remove('translate-x-full');
                body.classList.add('overflow-hidden');
            });

            closeModalButton.addEventListener('click', closeModal);

            document.addEventListener('click', (event) => {
                if (!modal.contains(event.target) && !button.contains(event.target)) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        }
    });
}
