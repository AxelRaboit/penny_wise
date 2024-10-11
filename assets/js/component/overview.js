function initializeOverviewModal() {
    const overviewBubble = document.getElementById('overviewBubble');
    const overviewModal = document.getElementById('overviewModal');
    const closeOverviewModal = document.getElementById('closeOverviewModal');
    const body = document.body;

    if (!overviewBubble || !overviewModal || !closeOverviewModal) {
        return;
    }

    const openModal = () => {
        overviewModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        overviewModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!overviewModal.contains(event.target) && !overviewBubble.contains(event.target)) {
            closeModal();
        }
    };

    overviewBubble.addEventListener('click', openModal);
    closeOverviewModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeOverviewModal);*/
document.addEventListener('turbo:load', initializeOverviewModal);
