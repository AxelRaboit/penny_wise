export function handlePageSpinner(contentId, spinnerId) {
    const content = document.getElementById(contentId);
    const spinner = document.getElementById(spinnerId);

    if (content && spinner) {
        spinner.classList.add('hidden');
        content.classList.remove('hidden');
    } else {
        console.error('Spinner or content element not found.');
    }
}
