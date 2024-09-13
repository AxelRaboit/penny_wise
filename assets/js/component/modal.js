const openModal = (modal) => {
    modal.classList.remove('hidden');
};

const closeModal = (modal) => {
    modal.classList.add('hidden');
};

const attachModalEvents = function({modalSelector, triggerButtonSelector, confirmButtonSelector, cancelButtonSelector}) {
    const modal = document.querySelector(modalSelector);
    const confirmButton = modal.querySelector(confirmButtonSelector);
    const cancelButton = modal.querySelector(cancelButtonSelector);
    let actionUrl = '';

    const handleConfirm = () => {
        if (actionUrl) {
            window.location.href = actionUrl;
        }
    };

    const handleCancel = () => {
        closeModal(modal);
    };

    const attachEventToTrigger = (triggerButton) => {
        triggerButton.addEventListener('click', function () {
            actionUrl = triggerButton.getAttribute('data-action-url');
            openModal(modal);
        });
    };

    const triggerButtons = document.querySelectorAll(triggerButtonSelector);
    triggerButtons.forEach(attachEventToTrigger);

    confirmButton.removeEventListener('click', handleConfirm);
    confirmButton.addEventListener('click', handleConfirm);

    cancelButton.removeEventListener('click', handleCancel);
    cancelButton.addEventListener('click', handleCancel);
};

export { attachModalEvents };
