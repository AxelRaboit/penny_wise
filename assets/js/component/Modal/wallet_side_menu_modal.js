function initializeModals() {
    const modals = [
        { buttonId: 'openWalletSideMenuNoteModal', modalId: 'walletSideMenuNoteModal', closeId: 'closeWalletSideMenuNoteModal' },
        { buttonId: 'openWalletSideMenuActionsModal', modalId: 'walletSideMenuActionsModal', closeId: 'closeWalletSideMenuActionsModal' },
        { buttonId: 'openWalletSideMenuGraphsModal', modalId: 'walletSideMenuGraphsModal', closeId: 'closeWalletSideMenuGraphsModal' },
        { buttonId: 'openWalletSideMenuCalendarModal', modalId: 'walletSideMenuCalendarModal', closeId: 'closeWalletSideMenuCalendarModal' },
        { buttonId: 'openWalletSideMenuSummaryModal', modalId: 'walletSideMenuSummaryModal', closeId: 'closeWalletSideMenuSummaryModal' },
        { buttonId: 'openWalletSideMenuInformationModal', modalId: 'walletSideMenuInformationModal', closeId: 'closeWalletSideMenuInformationModal' }
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
