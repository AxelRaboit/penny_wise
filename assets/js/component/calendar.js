function initializeCalendarModal() {
    const calendarBubble = document.getElementById('calendarBubble');
    const calendarModal = document.getElementById('calendarModal');
    const closeCalendarModal = document.getElementById('closeCalendarModal');
    const body = document.body;

    if (!calendarBubble || !calendarModal || !closeCalendarModal) {
        return;
    }

    const openModal = () => {
        calendarModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        calendarModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    const handleClickOutsideModal = (event) => {
        if (!calendarModal.contains(event.target) && !calendarBubble.contains(event.target)) {
            closeModal();
        }
    };

    calendarBubble.addEventListener('click', openModal);
    closeCalendarModal.addEventListener('click', closeModal);

    document.addEventListener('click', handleClickOutsideModal);

    window.addEventListener('beforeunload', closeModal);
}

/*document.addEventListener('DOMContentLoaded', initializeCalendarModal);*/
document.addEventListener('turbo:load', initializeCalendarModal);
