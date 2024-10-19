const toggleButton = document.querySelector('#js-toggle-sidebar-button');
let expanded = toggleButton.getAttribute('data-expanded') === 'true';

const sideMenuToggleButton = () => {
    toggleButton.addEventListener('click', function () {
        fetch('/user-settings/toggle-sidebar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                isCollapsed: !expanded
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                } else {
                    expanded = !expanded;
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    });
}

export const initializeSideMenu = () => {
    sideMenuToggleButton();
};
