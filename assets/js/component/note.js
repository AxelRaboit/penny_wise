function initializeNoteModal() {
    const noteBubble = document.getElementById('noteBubble');
    const noteModal = document.getElementById('noteModal');
    const closeNoteModal = document.getElementById('closeNoteModal');
    const body = document.body;

    if (!noteBubble || !noteModal || !closeNoteModal) {
        return;
    }

    const openModal = () => {
        noteModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        noteModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!noteModal.contains(event.target) && !noteBubble.contains(event.target)) {
            closeModal();
        }
    };

    noteBubble.addEventListener('click', openModal);
    closeNoteModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

document.addEventListener('DOMContentLoaded', initializeNoteModal);
/*document.addEventListener('turbo:load', initializeNoteModal);*/ // Tester avec Turbo
