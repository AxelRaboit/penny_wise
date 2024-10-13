import { initializeModals } from '../../../../../component/Modal/side_menu_modal.js';

export function initializeSideMenu() {
    const modalConfig = [
        {
            buttonId: 'openWalletSideMenuNoteModal',
            modalId: 'walletSideMenuNoteModal',
            closeId: 'closeWalletSideMenuNoteModal'
        },
        {
            buttonId: 'openWalletSideMenuActionsModal',
            modalId: 'walletSideMenuActionsModal',
            closeId: 'closeWalletSideMenuActionsModal'
        },
        {
            buttonId: 'openWalletSideMenuGraphsModal',
            modalId: 'walletSideMenuGraphsModal',
            closeId: 'closeWalletSideMenuGraphsModal'
        },
        {
            buttonId: 'openWalletSideMenuCalendarModal',
            modalId: 'walletSideMenuCalendarModal',
            closeId: 'closeWalletSideMenuCalendarModal'
        },
        {
            buttonId: 'openWalletSideMenuSummaryModal',
            modalId: 'walletSideMenuSummaryModal',
            closeId: 'closeWalletSideMenuSummaryModal'
        },
        {
            buttonId: 'openWalletSideMenuInformationModal',
            modalId: 'walletSideMenuInformationModal',
            closeId: 'closeWalletSideMenuInformationModal'
        }
    ];

    document.addEventListener('turbo:load', () => {
        initializeModals(modalConfig);
    });
}