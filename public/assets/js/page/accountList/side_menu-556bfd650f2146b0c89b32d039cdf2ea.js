import { initializeModals } from '../../component/Modal/side_menu_modal.js';

export function initializeAccountListSideMenu() {
    const modalConfig = [
        {
            buttonId: 'openAccountListSideMenuTestModal',
            modalId: 'accountListSideMenuTestModal',
            closeId: 'closeAccountListSideMenuTestModal'
        },
    ];

    document.addEventListener('turbo:load', () => {
        initializeModals(modalConfig);
    });
}