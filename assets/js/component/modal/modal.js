const attachOutsideClickListener = (modal) => {
  modal.addEventListener('click', function (e) {
    const content = modal.querySelector('.js-modal-content');
    if (!content.contains(e.target)) {
      closeModal(modal);
    }
  });
};

const createModal = ({
  modalId,
  modalTitle,
  modalMessageHeader,
  modalMessage = '',
  modalContent = '',
  confirmButtonId = null,
  cancelButtonId = null,
  confirmLabel = 'Yes',
  cancelLabel = 'No',
  isDeleteAction = false,
}) => {
  const existingModal = document.getElementById(modalId);
  if (existingModal) {
    return existingModal;
  }

  const deleteActionButtonClasses =
    'bg-transparent text-danger border-danger-ring hover:bg-danger hover:text-dynamic rounded-md border border-solid p-2';
  const validateActionButtonClasses =
    'bg-accent-primary text-dynamic border-accent-primary-ring hover:bg-accent-primary-hover rounded-md border border-solid p-2';
  const modal = document.createElement('div');
  modal.id = modalId;
  modal.classList.add(
    'fixed',
    'inset-0',
    'hidden',
    'bg-gray-900',
    'bg-opacity-50',
    'flex',
    'justify-center',
    'items-center',
    'p-4',
  );

  modal.innerHTML = `
        <div class="bg-tertiary rounded-md shadow-lg p-4 w-full md:w-1/3 max-w-lg border-solid border border-quaternary-ring js-modal-content">
            <h3 class="text-lg font-bold mb-4 text-dynamic">${modalTitle}</h3>
            <p class="mb-4 text-sm text-dynamic">${modalMessageHeader}</p>
            ${modalContent ? modalContent : `<p class="mb-4 text-text text-sm text-dynamic">${modalMessage}</p>`}
            <div class="flex justify-end">
                ${cancelButtonId ? `<button id="${cancelButtonId}" class="bg-quaternary hover:bg-quaternary-hover p-2 rounded-md mr-2 border-solid border border-quaternary-ring text-sm text-dynamic">${cancelLabel}</button>` : ''}
                ${confirmButtonId ? `<button id="${confirmButtonId}" class="${isDeleteAction ? deleteActionButtonClasses : validateActionButtonClasses} text-sm">${confirmLabel}</button>` : ''}
            </div>
        </div>
    `;

  document.body.appendChild(modal);

  attachOutsideClickListener(modal);

  return modal;
};

const openModal = (modal) => {
  modal.classList.remove('hidden');
};

const closeModal = (modal) => {
  modal.classList.add('hidden');
};

const attachModalEvents = function ({
  modalId,
  modalTitle,
  modalMessageHeader,
  modalMessage = '',
  modalContent = '',
  triggerButtonSelector,
  confirmButtonId = null,
  cancelButtonId = null,
  confirmLabel,
  cancelLabel,
  isDeleteAction = false,
}) {
  const modal = createModal({
    modalId,
    modalTitle,
    modalMessageHeader,
    modalMessage,
    modalContent,
    confirmButtonId,
    cancelButtonId,
    confirmLabel,
    cancelLabel,
    isDeleteAction,
  });

  const confirmButton = confirmButtonId
    ? document.getElementById(confirmButtonId)
    : null;
  const cancelButton = cancelButtonId
    ? document.getElementById(cancelButtonId)
    : null;
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

  if (confirmButton) {
    confirmButton.removeEventListener('click', handleConfirm);
    confirmButton.addEventListener('click', handleConfirm);
  }

  if (cancelButton) {
    cancelButton.removeEventListener('click', handleCancel);
    cancelButton.addEventListener('click', handleCancel);
  }
};

export { attachModalEvents };
