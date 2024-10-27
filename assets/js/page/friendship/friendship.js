document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('[data-tab]');
    const tabContents = document.querySelectorAll('[data-content]');
    const firstTab = 'friends';

    const setActiveTab = (tabName) => {
        tabButtons.forEach(button => {
            const isActive = button.getAttribute('data-tab') === tabName;
            button.classList.toggle('border-b-2', isActive);
            button.classList.toggle('border-accent-primary', isActive);
            button.classList.toggle('font-bold', isActive);
            button.classList.toggle('border-transparent', !isActive);
        });

        tabContents.forEach(content => {
            content.classList.toggle('hidden', content.getAttribute('data-content') !== tabName);
        });
    };

    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || firstTab;
    setActiveTab(initialTab);

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            setActiveTab(tabName);

            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', tabName);
            window.history.replaceState(null, '', newUrl);
        });
    });
});
