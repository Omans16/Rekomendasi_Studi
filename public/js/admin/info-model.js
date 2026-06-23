(function () {
    'use strict';

    const SELECTORS = {
        page: '.admin-info-page',
        chart: '#chart-fi',
        payload: 'featureImportancePayload'
    };

    let featureChart = null;

    function getElement(id) {
        return document.getElementById(id);
    }

    function getPageElement() {
        return document.querySelector(SELECTORS.page);
    }

    function isDarkMode() {
        return document.documentElement.dataset.theme === 'dark'
            || document.body.classList.contains('dark');
    }

    function getCssVariable(name, fallback) {
        const page = getPageElement() || document.documentElement;
        const value = getComputedStyle(page).getPropertyValue(name).trim();

        return value || fallback;
    }

    function getChartColors() {
        return {
            numeric: getCssVariable('--chart-numeric', '#f5c800'),
            derived: getCssVariable('--chart-derived', '#16a34a'),
            ohe: getCssVariable('--chart-ohe', '#d99a00')
        };
    }

    function getChartTheme() {
        const dark = isDarkMode();

        return {
            mode: dark ? 'dark' : 'light',
            text: getCssVariable('--text', dark ? '#f8fafc' : '#161616'),
            muted: getCssVariable('--muted', dark ? '#cbd5e1' : '#77705a'),
            grid: getCssVariable('--border', dark ? '#334155' : 'rgba(40, 40, 40, 0.10)')
        };
    }

    function parsePayload() {
        const payload = getElement(SELECTORS.payload);

        if (!payload) {
            return [];
        }

        try {
            const parsed = JSON.parse(payload.textContent.trim());
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function getFeatureTypeLabel(type) {
        const labels = {
            numeric: 'Fitur Numerik',
            derived: 'Fitur Turunan Agregat',
            ohe: 'Fitur OHE Jurusan'
        };

        return labels[type] || labels.numeric;
    }

    function getFeatureColor(type, colors) {
        const colorMap = {
            numeric: colors.numeric,
            derived: colors.derived,
            ohe: colors.ohe
        };

        return colorMap[type] || colors.numeric;
    }

    function showEmptyChart(chartElement) {
        const empty = document.createElement('div');

        empty.className = 'chart-empty';
        empty.textContent = 'Data feature importance belum tersedia.';

        chartElement.replaceChildren(empty);
    }

    function buildRows(rows) {
        return rows
            .map((row) => ({
                label: row.label || '-',
                value: Number.parseFloat(row.value) || 0,
                type: row.type || 'numeric'
            }))
            .sort((a, b) => b.value - a.value)
            .slice(0, 15);
    }

    function destroyChart() {
        if (featureChart) {
            featureChart.destroy();
            featureChart = null;
        }
    }

    function renderFeatureImportanceChart() {
        const chartElement = document.querySelector(SELECTORS.chart);

        if (!chartElement || typeof ApexCharts === 'undefined') {
            return;
        }

        const rows = buildRows(parsePayload());

        destroyChart();

        if (!rows.length) {
            showEmptyChart(chartElement);
            return;
        }

        const theme = getChartTheme();
        const colors = getChartColors();
        const labels = rows.map((row) => row.label);
        const values = rows.map((row) => Number.parseFloat(row.value.toFixed(6)));
        const barColors = rows.map((row) => getFeatureColor(row.type, colors));
        const dynamicHeight = Math.max(420, rows.length * 36);

        featureChart = new ApexCharts(chartElement, {
            series: [
                {
                    name: 'Importance',
                    data: values
                }
            ],
            chart: {
                type: 'bar',
                height: dynamicHeight,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit',
                foreColor: theme.text,
                background: 'transparent'
            },
            theme: {
                mode: theme.mode
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    distributed: true,
                    barHeight: '68%',
                    dataLabels: {
                        position: 'right'
                    }
                }
            },
            colors: barColors,
            dataLabels: {
                enabled: true,
                formatter: (value) => Number.parseFloat(value).toFixed(4),
                offsetX: 6,
                style: {
                    fontSize: '12px',
                    fontWeight: 700,
                    colors: [theme.text]
                }
            },
            xaxis: {
                categories: labels,
                labels: {
                    formatter: (value) => Number.parseFloat(value).toFixed(3),
                    style: {
                        fontSize: '12px',
                        colors: theme.muted
                    }
                }
            },
            yaxis: {
                labels: {
                    maxWidth: 230,
                    style: {
                        fontSize: '12px',
                        fontWeight: 700,
                        colors: theme.text
                    }
                }
            },
            grid: {
                borderColor: theme.grid,
                strokeDashArray: 4,
                padding: {
                    right: 38,
                    left: 8,
                    bottom: 10
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                theme: theme.mode,
                custom: function ({ dataPointIndex }) {
                    const item = rows[dataPointIndex];

                    return `
                        <div class="chart-tooltip">
                            <div class="chart-tooltip-title">${item.label}</div>
                            <div class="chart-tooltip-text">${getFeatureTypeLabel(item.type)}</div>
                            <div class="chart-tooltip-value">Importance: ${item.value.toFixed(6)}</div>
                        </div>
                    `;
                }
            }
        });

        featureChart.render();
    }

    function observeThemeChanges() {
        const observer = new MutationObserver(renderFeatureImportanceChart);

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme', 'class']
        });

        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    function init() {
        renderFeatureImportanceChart();
        observeThemeChanges();
    }

    document.addEventListener('DOMContentLoaded', init);
})();