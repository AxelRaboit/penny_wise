import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    this.element.addEventListener(
      'dropzone:connect',
      this._onConnect.bind(this),
    );
    this.element.addEventListener('dropzone:change', this._onChange.bind(this));
    this.element.addEventListener('dropzone:clear', this._onClear.bind(this));
  }

  _onConnect() {
    this.element.classList.add(
      'relative',
      'flex',
      'justify-center',
      'items-center',
      'h-48',
      'w-full',
      'border',
      'border-dashed',
      'border-quaternary',
      'rounded-md',
    );

    const textElement = document.createElement('p');
    textElement.textContent = 'Drag and drop files here or click to upload';
    textElement.classList.add(
      'drag-message',
      'text-dynamic-gray',
      'text-sm',
      'text-center',
    );
    textElement.style.position = 'absolute';
    this.element.appendChild(textElement);
  }

  _onChange() {
    this.element.classList.remove('border-dashed', 'border-quaternary');
    this.element.classList.add('border-solid', 'border-accent-primary');

    const dragMessage = this.element.querySelector('.drag-message');
    if (dragMessage) {
      dragMessage.style.display = 'none';
    }

    let successText = this.element.querySelector('.success-message');
    if (!successText) {
      successText = document.createElement('p');
      successText.classList.add(
        'success-message',
        'text-success',
        'absolute',
        'top-0',
        'left-0',
        'ml-2',
        'mt-2',
      );
      this.element.appendChild(successText);
    }
    successText.textContent = 'File uploaded successfully!';
  }

  _onClear() {
    this.element.classList.remove('border-accent-primary', 'border-solid');
    this.element.classList.add('border-dashed', 'border-quaternary');

    const dragMessage = this.element.querySelector('.drag-message');
    if (dragMessage) {
      dragMessage.style.display = 'block';
    }

    const successText = this.element.querySelector('.success-message');
    if (successText) {
      successText.remove();
    }
  }
}
