function initializeModals() {
    const modals = [
        { buttonId: 'openNoteModal', modalId: 'noteModal', closeId: 'closeNoteModal' },
        { buttonId: 'openActionsModal', modalId: 'actionsModal', closeId: 'closeActionsModal' },
        { buttonId: 'openGraphsModal', modalId: 'graphsModal', closeId: 'closeGraphsModal' },
        { buttonId: 'openCalendarModal', modalId: 'calendarModal', closeId: 'closeCalendarModal' },
        { buttonId: 'openOverviewModal', modalId: 'overviewModal', closeId: 'closeOverviewModal' },
        { buttonId: 'openInformationModal', modalId: 'informationModal', closeId: 'closeInformationModal' }
    ];

    const body = document.body;

    modals.forEach(({ buttonId, modalId, closeId }) => {
        const button = document.getElementById(buttonId);
        const modal = document.getElementById(modalId);
        const closeModalButton = document.getElementById(closeId);

        if (button && modal && closeModalButton) {
            button.addEventListener('click', () => {
                modal.classList.remove('translate-x-full');
                body.classList.add('overflow-hidden');
            });

            closeModalButton.addEventListener('click', () => {
                modal.classList.add('translate-x-full');
                body.classList.remove('overflow-hidden');
            });

            document.addEventListener('click', (event) => {
                if (!modal.contains(event.target) && !button.contains(event.target)) {
                    modal.classList.add('translate-x-full');
                    body.classList.remove('overflow-hidden');
                }
            });
        }
    });
}

document.addEventListener('turbo:load', initializeModals);
