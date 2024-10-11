function initializeInformationModal() {
    const informationBubble = document.getElementById('informationBubble');
    const informationModal = document.getElementById('informationModal');
    const closeInformationModal = document.getElementById('closeInformationModal');
    const body = document.body;

    if (!informationBubble || !informationModal || !closeInformationModal) {
        return;
    }

    const openModal = () => {
        informationModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        informationModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!informationModal.contains(event.target) && !informationBubble.contains(event.target)) {
            closeModal();
        }
    };

    informationBubble.addEventListener('click', openModal);
    closeInformationModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeInformationModal);*/
document.addEventListener('turbo:load', initializeInformationModal);
