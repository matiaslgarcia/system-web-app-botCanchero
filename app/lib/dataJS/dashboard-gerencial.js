const dataNode = document.getElementById('dashboard-gerencial-data');
const exportDashboardPdfButton = document.getElementById('btnExportarDashboardPDF');
const printDashboardButton = document.getElementById('btnImprimirDashboard');

const parseData = () => {
    if (!dataNode) return null;
    try {
        return JSON.parse(dataNode.textContent || '{}');
    } catch (error) {
        console.error('No se pudo parsear la data del dashboard gerencial', error);
        return null;
    }
};

const renderFallback = (elementId, message) => {
    const element = document.getElementById(elementId);
    if (!element) return;
    element.innerHTML = `<div class="d-flex h-100 align-items-center justify-content-center text-muted fs-6">${message}</div>`;
};

const buildChart = (elementId, options, emptyMessage) => {
    const element = document.getElementById(elementId);
    if (!element) return;
    if (typeof window.ApexCharts === 'undefined') {
        renderFallback(elementId, 'Visualización no disponible en esta instalación.');
        return;
    }
    if (!options || !Array.isArray(options.series) || options.series.length === 0) {
        renderFallback(elementId, emptyMessage);
        return;
    }
    const chart = new window.ApexCharts(element, options);
    chart.render();
};

const dashboardData = parseData();
if (!dashboardData) {
    ['chart-commercial-trend', 'chart-payment-mix', 'chart-occupancy-bucket', 'chart-field-revenue'].forEach((id) => {
        renderFallback(id, 'No hay datos para visualizar.');
    });
} else {
    const trend = dashboardData.dailySeries || [];
    const paymentMix = dashboardData.paymentMix || [];
    const occupancyBuckets = dashboardData.occupancyBuckets || [];
    const fieldRevenue = dashboardData.fieldRevenue || [];

    buildChart('chart-commercial-trend', {
        chart: {
            type: 'line',
            height: 340,
            toolbar: { show: false },
        },
        stroke: {
            curve: 'smooth',
            width: [3, 3, 3, 4],
        },
        series: [
            {
                name: 'Reservas',
                data: trend.map((row) => Number(row.booking || 0)),
            },
            {
                name: 'Extra',
                data: trend.map((row) => Number(row.extra || 0)),
            },
            {
                name: 'Egresos',
                data: trend.map((row) => Number(row.expense || 0)),
            },
            {
                name: 'Neto',
                data: trend.map((row) => Number(row.net || 0)),
            },
        ],
        xaxis: {
            categories: trend.map((row) => row.label || ''),
            labels: { rotate: -45 },
        },
        colors: ['#009EF7', '#50CD89', '#F1416C', '#7239EA'],
        yaxis: {
            labels: {
                formatter: (value) => `$${Number(value || 0).toFixed(0)}`,
            },
        },
        tooltip: {
            y: {
                formatter: (value) => `$${Number(value || 0).toFixed(2)}`,
            },
        },
        legend: {
            position: 'top',
        },
        grid: {
            borderColor: '#EFF2F5',
        },
    }, 'No hay tendencia diaria para el período seleccionado.');

    buildChart('chart-payment-mix', {
        chart: {
            type: 'donut',
            height: 340,
        },
        labels: paymentMix.map((row) => row.label || ''),
        series: paymentMix.map((row) => Number(row.value || 0)),
        colors: ['#009EF7', '#50CD89', '#FFC700', '#7239EA', '#F1416C', '#181C32', '#7E8299'],
        tooltip: {
            y: {
                formatter: (value) => `$${Number(value || 0).toFixed(2)}`,
            },
        },
        legend: {
            position: 'bottom',
        },
        dataLabels: {
            formatter: (_, opts) => {
                const item = paymentMix[opts.seriesIndex] || {};
                return `${Number(item.share || 0).toFixed(1)}%`;
            },
        },
    }, 'No hay mix de pagos para mostrar.');

    buildChart('chart-occupancy-bucket', {
        chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                distributed: true,
            },
        },
        series: [{
            name: 'Ocupación',
            data: occupancyBuckets.map((row) => Number(row.rate || 0)),
        }],
        xaxis: {
            categories: occupancyBuckets.map((row) => row.label || ''),
        },
        yaxis: {
            max: 100,
            labels: {
                formatter: (value) => `${Number(value || 0).toFixed(0)}%`,
            },
        },
        colors: ['#009EF7', '#50CD89', '#FFC700', '#F1416C'],
        tooltip: {
            y: {
                formatter: (value) => `${Number(value || 0).toFixed(2)}%`,
            },
        },
        legend: { show: false },
        grid: {
            borderColor: '#EFF2F5',
        },
    }, 'No hay ocupación por franja para graficar.');

    buildChart('chart-field-revenue', {
        chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
            },
        },
        series: [{
            name: 'Facturación',
            data: fieldRevenue.map((row) => Number(row.value || 0)),
        }],
        xaxis: {
            categories: fieldRevenue.map((row) => row.label || ''),
            labels: {
                formatter: (value) => `$${Number(value || 0).toFixed(0)}`,
            },
        },
        colors: ['#50CD89'],
        tooltip: {
            y: {
                formatter: (value) => `$${Number(value || 0).toFixed(2)}`,
            },
        },
        legend: { show: false },
        grid: {
            borderColor: '#EFF2F5',
        },
    }, 'No hay facturación por cancha para graficar.');
}

if (exportDashboardPdfButton) {
    exportDashboardPdfButton.addEventListener('click', () => {
        const url = exportDashboardPdfButton.dataset.url || '';
        if (!url) return;
        window.open(url, '_blank', 'noopener');
    });
}

if (printDashboardButton) {
    printDashboardButton.addEventListener('click', () => {
        window.print();
    });
}
