const createModal = ({modalId, modalTitle, modalMessageHeader, modalMessage = '', confirmButtonId, cancelButtonId, confirmLabel = 'Yes', cancelLabel = 'No'}) => {
    let existingModal = document.getElementById(modalId);
    if (existingModal) {
        return existingModal;
    }

    const modal = document.createElement('div');
    modal.id = modalId;
    modal.classList.add('fixed', 'inset-0', 'hidden', 'bg-gray-900', 'bg-opacity-50', 'flex', 'justify-center', 'items-center');

    modal.innerHTML = `
        <div class="bg-tertiary rounded-md shadow-lg p-4 w-1/3 border-solid border border-quaternary-ring">
            <h3 class="text-lg text-white font-bold mb-4">${modalTitle}</h3>
            <p class="mb-4 text-white">${modalMessageHeader}</p>
            ${modalMessage ? `<p class="mb-4 text-sm text-white">${modalMessage}</p>` : ''}
            <div class="flex justify-end">
                <button id="${cancelButtonId}" class="bg-quaternary hover:bg-quaternary-hover text-white p-2 rounded-md mr-2 border-solid border border-quaternary-ring">${cancelLabel}</button>
                <button id="${confirmButtonId}" class="bg-quinary hover:bg-quinary-hover text-white p-2 rounded-md border-solid border border-quaternary-ring">${confirmLabel}</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    return modal;
};

const openModal = (modal) => {
    modal.classList.remove('hidden');
};

const closeModal = (modal) => {
    modal.classList.add('hidden');
};

const attachModalEvents = function({
   modalId,
   modalTitle,
   modalMessageHeader,
   modalMessage = '',
   triggerButtonSelector,
   confirmButtonId,
   cancelButtonId,
   confirmLabel,
   cancelLabel,
}) {
    const modal = createModal({
        modalId,
        modalTitle,
        modalMessageHeader,
        modalMessage,
        confirmButtonId,
        cancelButtonId,
        confirmLabel,
        cancelLabel
    });
    const confirmButton = document.getElementById(confirmButtonId);
    const cancelButton = document.getElementById(cancelButtonId);
    let actionUrl = '';

    const handleConfirm = () => {
        if (actionUrl) {
            window.location.href = actionUrl;
        }
    };

    const handleCancel = () => {
        closeModal(modal);
    };

    const attachEventToTrigger = (triggerButton) => {
        triggerButton.addEventListener('click', function () {
            actionUrl = triggerButton.getAttribute('data-action-url');
            openModal(modal);
        });
    };

    const triggerButtons = document.querySelectorAll(triggerButtonSelector);
    triggerButtons.forEach(attachEventToTrigger);

    confirmButton.removeEventListener('click', handleConfirm);
    confirmButton.addEventListener('click', handleConfirm);

    cancelButton.removeEventListener('click', handleCancel);
    cancelButton.addEventListener('click', handleCancel);
};

export { attachModalEvents };
