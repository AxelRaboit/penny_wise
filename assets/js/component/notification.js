function initializeNotificationModal() {
    const notificationBubble = document.getElementById('notificationBubble');
    const notificationModal = document.getElementById('notificationModal');
    const closeNotificationModal = document.getElementById('closeNotificationModal');
    const body = document.body;

    if (!notificationBubble || !notificationModal || !closeNotificationModal) {
        return;
    }

    const openModal = () => {
        notificationModal.classList.remove('translate-x-full');
        body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        notificationModal.classList.add('translate-x-full');
        body.classList.remove('overflow-hidden');
    };

    notificationBubble.addEventListener('click', openModal);
    closeNotificationModal.addEventListener('click', closeModal);

    document.addEventListener('click', function(event) {
        const target = event.target;
        if (target.tagName === 'A' && target.href) {
            closeModal();
        }
    });

    window.addEventListener('beforeunload', closeModal);
}

document.addEventListener('DOMContentLoaded', initializeNotificationModal);

document.addEventListener('turbo:load', initializeNotificationModal);
