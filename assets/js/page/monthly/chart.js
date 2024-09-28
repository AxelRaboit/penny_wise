export function initializeChartButtons() {
    document.querySelectorAll('.monthly-chart-button').forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.getAttribute('data-chart-type');
            const chartId = this.getAttribute('data-chart-id');
            const year = this.getAttribute('data-year');
            const month = this.getAttribute('data-month');
            const chartFormat = this.getAttribute('data-chart-format') || 'bar';

            fetch(`/api/get-chart-data?type=${chartType}&year=${year}&month=${month}&format=${chartFormat}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.chartHtml) {
                        document.getElementById(chartId).innerHTML = data.chartHtml;
                    } else {
                        console.error('Error: No chartHtml in response.');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
}
