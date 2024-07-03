import { showModal } from 'modal';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-transaction-button').forEach(button => {
        button.addEventListener('click', () => {
            loadFormInModal('/transaction/new');
        });
    });
});

function loadFormInModal(url) {
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
                attachFormSubmitEvent();
            } else {
                console.error('Unexpected response status:', data.status);
            }
        })
        .catch(error => {
            console.error('Error loading form:', error);
            alert('An error occurred while loading the form.');
        });
}

function attachFormSubmitEvent() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm(form);
        });
    }
}

function submitForm(form) {
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
