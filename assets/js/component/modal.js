export function showModal(content) {
    const app = document.getElementById('app');

    const modal = document.createElement('div');
    modal.classList.add('fixed', 'inset-0', 'z-50', 'flex', 'items-center', 'justify-center');
    modal.id = 'modal';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    app.appendChild(modal);

    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content', 'relative', 'bg-white', 'rounded-lg', 'p-4', 'shadow-lg', 'flex', 'flex-col', 'items-center', 'justify-center');
    modal.appendChild(modalContent);

    modalContent.innerHTML = content;

    const modalButton = document.createElement('button');
    modalButton.classList.add('absolute', 'top-0', 'right-0', 'cursor-pointer');
    modalButton.innerHTML = '<i class="fa-solid fa-circle-xmark text-secondary p-1"></i>';
    modalContent.appendChild(modalButton);

    modalButton.addEventListener('click', () => {
        modal.remove();
    });
}
