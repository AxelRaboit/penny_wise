const requiredInputs = document.querySelectorAll('input[required], select[required]');
if (requiredInputs.length > 0) {
    requiredInputs.forEach(input => {
        let label = input.closest('div').querySelector('label');
        if (!label) {
            label = document.querySelector(`label[for="${input.id}"]`);
        }

        if (label) {
            if (!label.querySelector('.asterisk')) {
                const asterisk = document.createElement('span');
                asterisk.classList.add('asterisk', 'text-danger', 'text-sm', 'font-bold');
                asterisk.innerHTML = '*';
                label.appendChild(asterisk);
            }
        }
    });
}