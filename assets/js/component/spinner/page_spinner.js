export function handlePageSpinner(contentId, spinnerId, extraClasses = null) {
  const content = document.getElementById(contentId);
  const spinner = document.getElementById(spinnerId);

  if (content && spinner) {
    spinner.classList.add('hidden');
    content.classList.remove('hidden');

    if (extraClasses) {
      content.classList.add(...extraClasses.split(' '));
    }
  } else {
    console.error('Spinner or content element not found.');
  }
}
