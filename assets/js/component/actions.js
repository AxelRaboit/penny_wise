function initializeActionsModal() {
    const actionsBubble = document.getElementById('actionsBubble');
    const actionsModal = document.getElementById('actionsModal');
    const closeActionsModal = document.getElementById('closeActionsModal');
    const body = document.body;

    if (!actionsBubble || !actionsModal || !closeActionsModal) {
        return;
    }

    const openModal = () => {
        actionsModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        actionsModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!actionsModal.contains(event.target) && !actionsBubble.contains(event.target)) {
            closeModal();
        }
    };

    actionsBubble.addEventListener('click', openModal);
    closeActionsModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeActionsModal);*/
document.addEventListener('turbo:load', initializeActionsModal);
