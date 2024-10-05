import {attachModalEvents} from "../../component/modal.js";

export function initializeWalletListModals() {
    attachModalEvents({
        modalId: 'deleteWalletModal',
        modalTitle: 'Delete month\'s wallet and transactions',
        modalMessage: 'Are you sure you want to delete the monthly wallet?',
        triggerButtonSelector: '.js-delete-wallet-button',
        confirmButtonId: 'confirmDeleteWallet',
        confirmLabel: 'Delete',
        cancelButtonId: 'cancelDeleteWallet',
        cancelLabel: 'Cancel',
    });

    attachModalEvents({
        modalId: 'deleteAccountModal',
        modalTitle: 'Delete account',
        modalMessage: 'Are you sure you want to delete the account?',
        triggerButtonSelector: '.js-delete-account-button',
        confirmButtonId: 'confirmDeleteAccount',
        confirmLabel: 'Delete',
        cancelButtonId: 'cancelDeleteAccount',
        cancelLabel: 'Cancel',
    });
}