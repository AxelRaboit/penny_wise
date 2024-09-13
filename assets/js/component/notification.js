document.addEventListener('DOMContentLoaded', function() {
    const notificationBubble = document.getElementById('notificationBubble');
    const notificationModal = document.getElementById('notificationModal');
    const closeNotificationModal = document.getElementById('closeNotificationModal');
    const body = document.body;

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
});