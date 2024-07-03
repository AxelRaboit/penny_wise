export function showModal(content) {
    const app = document.getElementById('app');

    const modal = document.createElement('div');
    modal.classList.add('fixed', 'inset-0', 'z-50', 'flex', 'items-center', 'justify-center');
    modal.id = 'modal';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    app.appendChild(modal);

    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content', 'bg-white', 'rounded-lg', 'p-4', 'shadow-lg', 'flex', 'flex-col', 'items-center', 'justify-center');
    modal.appendChild(modalContent);

    modalContent.innerHTML = content;

    const modalButton = document.createElement('button');
    modalButton.classList.add('bg-primary', 'text-white', 'py-2', 'px-4', 'rounded', 'mt-4');
    modalButton.textContent = 'Close';
    modalContent.appendChild(modalButton);

    modalButton.addEventListener('click', () => {
        modal.remove();
    });
}
