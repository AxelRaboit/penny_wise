import { showModal } from 'modal';

export const loadTransactionFormInModal = (url) => {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                console.error(`HTTP error! status: ${response.status}`);
                return response.text().then(text => {
                    console.error(`Response text: ${text}`);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'form') {
                showModal(data.form);
                attachTransactionFormSubmitEvent();
            } else {
                console.error('Unexpected response status:', data.status);
            }
        })
        .catch(error => {
            console.error('Error loading form:', error);
            alert('An error occurred while loading the form.');
        });
}

const attachTransactionFormSubmitEvent = () => {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitTransactionForm(form);
        });
    }
}

const submitTransactionForm = (form) => {
    const formData = new FormData(form);
    fetch('/transaction/new/submission', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error(`HTTP error! status: ${response.status}, body: ${text}`);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
            if (data.status === 'success') {
                // TODO: add alertify
                document.querySelector('#modal').remove();
            } else if (data.status === 'error') {
                console.error('Error:', data.message);
            } else {
                console.error('Unexpected response status:', data.status);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
