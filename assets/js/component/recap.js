function initializeRecapModal() {
    const recapBubble = document.getElementById('recapBubble');
    const recapModal = document.getElementById('recapModal');
    const closeRecapModal = document.getElementById('closeRecapModal');
    const body = document.body;

    if (!recapBubble || !recapModal || !closeRecapModal) {
        return;
    }

    const openModal = () => {
        recapModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        recapModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!recapModal.contains(event.target) && !recapBubble.contains(event.target)) {
            closeModal();
        }
    };

    recapBubble.addEventListener('click', openModal);
    closeRecapModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeRecapModal);*/
document.addEventListener('turbo:load', initializeRecapModal);
