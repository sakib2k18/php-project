import { $$ } from '../bootstrap';

/**
 * Admin dashboard charts.
 *
 * Every dataset is rendered into a <canvas data-chart> element by Blade from
 * live MySQL aggregates — nothing here invents a number. Chart.js is imported
 * dynamically so the public site never downloads it.
 */
const PALETTE = {
    brand: '#0f7d5a',
    brandSoft: 'rgba(15,125,90,.12)',
    accent: '#fb8710',
    accentSoft: 'rgba(251,135,16,.14)',
    ink: '#8d9793',
    grid: 'rgba(220,223,220,.7)',
    series: ['#0f7d5a', '#fb8710', '#3fb98c', '#ffc770', '#0c644a', '#ec6a06', '#75d3ae', '#9c3e0f', '#1c9d70'],
    status: ['#f59e0b', '#0f7d5a', '#e11d48'],
};

export default function initCharts() {
    const canvases = $$('canvas[data-chart]');
    if (!canvases.length) return;

    import('chart.js/auto')
        .then(({ default: Chart }) => {
            Chart.defaults.font.family = "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = PALETTE.ink;
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.boxWidth = 8;
            Chart.defaults.plugins.legend.labels.padding = 14;

            canvases.forEach((canvas) => build(Chart, canvas));
        })
        .catch(() => {
            canvases.forEach((canvas) => {
                canvas.closest('[data-chart-wrap]')?.insertAdjacentHTML(
                    'beforeend',
                    '<p class="p-4 text-center text-xs text-ink-500">Chart could not be loaded.</p>'
                );
            });
        });
}

function build(Chart, canvas) {
    const labels = parse(canvas.dataset.chartLabels) || [];
    const values = parse(canvas.dataset.chartValues) || [];
    const second = parse(canvas.dataset.chartValues2);
    const type = canvas.dataset.chart;
    const currency = canvas.dataset.chartCurrency || '';

    const money = (value) => `${currency} ${Number(value).toLocaleString()}`.trim();

    const tooltip = {
        backgroundColor: '#1b201e',
        padding: 11,
        cornerRadius: 8,
        titleFont: { weight: '700', size: 12 },
        bodyFont: { size: 12 },
        displayColors: false,
        callbacks: {
            label: (item) => {
                const raw = item.raw ?? 0;
                const suffix = canvas.dataset.chartMoney === 'true' ? money(raw) : Number(raw).toLocaleString();
                return `${item.dataset.label ?? ''} ${suffix}`.trim();
            },
        },
    };

    if (type === 'line' || type === 'bar') {
        const datasets = [
            {
                label: canvas.dataset.chartLabel || '',
                data: values,
                borderColor: PALETTE.brand,
                backgroundColor: type === 'bar' ? PALETTE.brand : PALETTE.brandSoft,
                borderWidth: type === 'line' ? 2.5 : 0,
                fill: type === 'line',
                tension: 0.38,
                pointRadius: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: PALETTE.brand,
                pointBorderWidth: 2,
                pointHoverRadius: 5,
                borderRadius: type === 'bar' ? 6 : 0,
                maxBarThickness: 42,
            },
        ];

        if (second) {
            datasets.push({
                label: canvas.dataset.chartLabel2 || '',
                data: second,
                borderColor: PALETTE.accent,
                backgroundColor: type === 'bar' ? PALETTE.accent : PALETTE.accentSoft,
                borderWidth: type === 'line' ? 2.5 : 0,
                fill: false,
                tension: 0.38,
                pointRadius: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: PALETTE.accent,
                pointBorderWidth: 2,
                borderRadius: type === 'bar' ? 6 : 0,
                maxBarThickness: 42,
            });
        }

        new Chart(canvas, {
            type,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: Boolean(second), position: 'bottom' },
                    tooltip,
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: PALETTE.grid, drawTicks: false },
                        ticks: {
                            padding: 8,
                            callback: (value) =>
                                value >= 1000 ? `${(value / 1000).toFixed(value % 1000 === 0 ? 0 : 1)}k` : value,
                        },
                    },
                },
            },
        });

        return;
    }

    if (type === 'doughnut' || type === 'pie') {
        const colours = canvas.dataset.chartPalette === 'status' ? PALETTE.status : PALETTE.series;

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: labels.map((_, i) => colours[i % colours.length]),
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: type === 'doughnut' ? '64%' : 0,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        ...tooltip,
                        callbacks: {
                            label: (item) => {
                                const total = item.dataset.data.reduce((a, b) => a + Number(b), 0) || 1;
                                const share = ((Number(item.raw) / total) * 100).toFixed(1);
                                const amount =
                                    canvas.dataset.chartMoney === 'true'
                                        ? money(item.raw)
                                        : Number(item.raw).toLocaleString();
                                return `${item.label}: ${amount} (${share}%)`;
                            },
                        },
                    },
                },
            },
        });
    }
}

function parse(value) {
    try {
        return value ? JSON.parse(value) : null;
    } catch {
        return null;
    }
}
