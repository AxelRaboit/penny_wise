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

    noteBubble.addEventListener('click', openModal);
    closeNoteModal.addEventListener('click', closeModal);

    document.addEventListener('click', function(event) {
        const target = event.target;
        if (target.tagName === 'A' && target.href) {
            closeModal();
        }
    });

    window.addEventListener('beforeunload', closeModal);
}

document.addEventListener('DOMContentLoaded', initializeNoteModal);

document.addEventListener('turbo:load', initializeNoteModal);
