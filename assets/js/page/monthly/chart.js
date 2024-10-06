export function initializeChartButtons() {
    document.querySelectorAll('.month-chart-data-button').forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.getAttribute('data-chart-type');
            const chartId = this.getAttribute('data-chart-id');
            const year = this.getAttribute('data-year');
            const month = this.getAttribute('data-month');
            const chartFormat = this.getAttribute('data-chart-format') || 'bar';
            const accountId = this.getAttribute('data-account-id');

            if (!accountId) {
                console.error('Account ID is missing for chart request');
                return;
            }
            if (!chartId) {
                console.error('Chart ID is missing for chart update');
                return;
            }
            if (!chartType) {
                console.error('Chart type is missing for chart request');
                return;
            }
            if (!year || !month) {
                console.error('Year or month is missing for chart request');
                return;
            }

            const url = `/account/${accountId}/wallet/chart/data?type=${chartType}&year=${year}&month=${month}&format=${chartFormat}`;

            fetch(url)
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
