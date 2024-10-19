import {attachModalEvents} from "../../component/Modal/modal.js";

export function initializeWalletListModals() {
    attachModalEvents({
        modalId: 'deleteAccountModal',
        modalTitle: 'Delete account',
        modalMessageHeader: 'Are you sure you want to delete the account?',
        modalMessage: 'This action will also delete all of the wallets and transactions associated with this account.',
        triggerButtonSelector: '.js-delete-account-button',
        confirmButtonId: 'confirmDeleteAccount',
        confirmLabel: 'Delete',
        cancelButtonId: 'cancelDeleteAccount',
        cancelLabel: 'Cancel',
    });

    attachModalEvents({
        modalId: 'deleteWalletModal',
        modalTitle: 'Delete month and all of it\'s transactions',
        modalMessageHeader: 'Are you sure you want to delete this month?',
        modalMessage: 'This action will also delete all of the transactions associated with this month.',
        triggerButtonSelector: '.js-delete-wallet-button',
        confirmButtonId: 'confirmDeleteWallet',
        confirmLabel: 'Delete',
        cancelButtonId: 'cancelDeleteWallet',
        cancelLabel: 'Cancel',
    });

    attachModalEvents({
        modalId: 'deleteYearModal',
        modalTitle: 'Delete year and all of it\'s months and transactions',
        modalMessageHeader: 'Are you sure you want to delete this year?',
        modalMessage: 'This action will also delete all of the months and transactions associated with this year.',
        triggerButtonSelector: '.js-delete-year-button',
        confirmButtonId: 'confirmDeleteYear',
        confirmLabel: 'Delete',
        cancelButtonId: 'cancelDeleteYear',
        cancelLabel: 'Cancel',
    });
}