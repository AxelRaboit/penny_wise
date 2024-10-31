document.getElementById('profile-search-friends').addEventListener('input', function() {
    const query = this.value;

    fetch(`/profile/friendship/search?query=${encodeURIComponent(query)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.text())
        .then(html => {
            document.getElementById('friends-list').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
});
