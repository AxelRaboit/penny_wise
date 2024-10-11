function initializeGraphsModal() {
    const graphsBubble = document.getElementById('graphsBubble');
    const graphsModal = document.getElementById('graphsModal');
    const closeGraphsModal = document.getElementById('closeGraphsModal');
    const body = document.body;

    if (!graphsBubble || !graphsModal || !closeGraphsModal) {
        return;
    }

    const openModal = () => {
        graphsModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        graphsModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!graphsModal.contains(event.target) && !graphsBubble.contains(event.target)) {
            closeModal();
        }
    };

    graphsBubble.addEventListener('click', openModal);
    closeGraphsModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeRecapModal);*/
document.addEventListener('turbo:load', initializeGraphsModal);
