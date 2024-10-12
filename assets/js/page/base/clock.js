export function clock() {
    const currentTimeElement = document.getElementById('clock');
    const spinnerElement = document.querySelector('#clockContainer .spinner');

    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');

    const day = now.getDate().toString().padStart(2, '0');
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const year = now.getFullYear();

    const time = `${hours}:${minutes}:${seconds}`;
    const date = `${day}/${month}/${year}`;

    currentTimeElement.textContent = `${date} ${time}`;

    // Hide the spinner and show the clock
    spinnerElement.classList.add('hidden');
    currentTimeElement.classList.remove('hidden');
}

export function initializeClock() {
    setInterval(clock, 1000);
}

document.addEventListener('DOMContentLoaded', initializeClock);
