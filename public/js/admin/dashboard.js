(function () {
    'use strict';

    const SELECTORS = {
        page: '.dashboard-page',
        payload: 'dashboardChartPayload',
        chartSmk: 'chartSmk',
        chartUniv: 'chartUniv',
        chartJurKuliah: 'chartJurKuliah'
    };

    const CHART_KEYS = {
        smk: 'smk',
        univ: 'univ',
        program: 'program'
    };

    const chartInstances = {
        smk: null,
        univ: null,
        program: null
    };

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
        const page = getPageElement();
        const fromPage = page
            ? getComputedStyle(page).getPropertyValue(name).trim()
            : '';

        if (fromPage) {
            return fromPage;
        }

        const fromRoot = getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim();

        return fromRoot || fallback;
    }

    function getChartTheme() {
        const dark = isDarkMode();

        return {
            mode: dark ? 'dark' : 'light',
            text: getCssVariable('--text', dark ? '#f8fafc' : '#161616'),
            muted: getCssVariable('--muted', dark ? '#cbd5e1' : '#77705a'),
            grid: getCssVariable('--border', dark ? '#334155' : 'rgba(40, 40, 40, 0.10)'),
            primary: getCssVariable('--chart-primary', '#f5c800'),
            secondary: getCssVariable('--chart-secondary', '#d99a00')
        };
    }

    function parsePayload() {
        const payload = getElement(SELECTORS.payload);

        if (!payload) {
            return {
                topUniv: [],
                topJurKuliah: [],
                alumniSmk: {}
            };
        }

        try {
            const parsedPayload = JSON.parse(payload.textContent.trim());

            return {
                topUniv: parsedPayload.topUniv || [],
                topJurKuliah: parsedPayload.topJurKuliah || [],
                alumniSmk: parsedPayload.alumniSmk || {}
            };
        } catch (error) {
            return {
                topUniv: [],
                topJurKuliah: [],
                alumniSmk: {}
            };
        }
    }

    function normalizeArray(data) {
        if (Array.isArray(data)) {
            return data;
        }

        if (data && typeof data === 'object') {
            return Object.values(data);
        }

        return [];
    }

    function destroyChart(chartKey) {
        if (chartInstances[chartKey]) {
            chartInstances[chartKey].destroy();
            chartInstances[chartKey] = null;
        }
    }

    function showEmptyChart(element) {
        const empty = document.createElement('div');

        empty.className = 'chart-empty';
        empty.textContent = 'Data grafik belum tersedia.';

        element.replaceChildren(empty);
    }

    function createTooltip(title, subtitle, value) {
        return `
            <div class="chart-tooltip">
                <div class="chart-tooltip-title">${title}</div>
                ${subtitle ? `<div class="chart-tooltip-text">${subtitle}</div>` : ''}
                <div class="chart-tooltip-value">${value} alumni</div>
            </div>
        `;
    }

    function createHorizontalBarChart(options) {
        const theme = getChartTheme();
        const dynamicHeight = Math.max(options.minHeight, options.rows.length * options.rowHeight);

        return new ApexCharts(options.element, {
            series: [
                {
                    name: 'Jumlah Alumni',
                    data: options.values
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
                    barHeight: '68%',
                    dataLabels: {
                        position: 'right'
                    }
                }
            },
            colors: [options.color || theme.primary],
            dataLabels: {
                enabled: true,
                formatter: function (value) {
                    return `${value} alumni`;
                },
                offsetX: 6,
                style: {
                    fontSize: '12px',
                    fontWeight: 700,
                    colors: [theme.text]
                }
            },
            xaxis: {
                categories: options.labels,
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: theme.muted
                    }
                }
            },
            yaxis: {
                labels: {
                    maxWidth: options.yAxisMaxWidth,
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
                    right: 36,
                    left: 8
                }
            },
            tooltip: {
                theme: theme.mode,
                custom: function ({ dataPointIndex }) {
                    const row = options.rows[dataPointIndex];

                    return createTooltip(
                        row.tooltipTitle,
                        row.tooltipSubtitle,
                        row.jumlah
                    );
                }
            },
            legend: {
                show: false
            }
        });
    }

    function buildUniversityRows(data) {
        return normalizeArray(data)
            .map((item) => ({
                label: item.universitas || item.nama_universitas || '-',
                jumlah: Number(item.jumlah || item.total || 0)
            }))
            .sort((a, b) => b.jumlah - a.jumlah)
            .slice(0, 10)
            .map((item) => ({
                ...item,
                tooltipTitle: item.label,
                tooltipSubtitle: ''
            }));
    }

    function buildProgramRows(data) {
        return normalizeArray(data)
            .map((item) => ({
                label: item.program_studi || item.jurusan_kuliah || item.Jurusan_Kuliah || '-',
                jumlah: Number(item.jumlah || item.total || 0)
            }))
            .sort((a, b) => b.jumlah - a.jumlah)
            .slice(0, 10)
            .map((item) => ({
                ...item,
                tooltipTitle: item.label,
                tooltipSubtitle: ''
            }));
    }

    function buildSmkRows(data) {
        if (!data || typeof data !== 'object') {
            return [];
        }

        return Object.keys(data)
            .map((key) => ({
                label: key,
                fullLabel: data[key].nama_lengkap || key,
                jumlah: Number(data[key].jumlah_alumni || data[key].jumlah || 0)
            }))
            .sort((a, b) => b.jumlah - a.jumlah)
            .map((item) => ({
                ...item,
                tooltipTitle: item.label,
                tooltipSubtitle: item.fullLabel
            }));
    }

    function renderChart(chartKey, elementId, rows, chartOptions) {
        const element = getElement(elementId);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        destroyChart(chartKey);

        if (!rows.length) {
            showEmptyChart(element);
            return;
        }

        const chart = createHorizontalBarChart({
            element,
            rows,
            labels: rows.map((row) => row.label),
            values: rows.map((row) => row.jumlah),
            ...chartOptions
        });

        chartInstances[chartKey] = chart;
        chart.render();
    }

    function renderCharts() {
        const payload = parsePayload();
        const theme = getChartTheme();

        renderChart(
            CHART_KEYS.smk,
            SELECTORS.chartSmk,
            buildSmkRows(payload.alumniSmk),
            {
                color: theme.primary,
                minHeight: 360,
                rowHeight: 34,
                yAxisMaxWidth: 120
            }
        );

        renderChart(
            CHART_KEYS.univ,
            SELECTORS.chartUniv,
            buildUniversityRows(payload.topUniv),
            {
                color: theme.primary,
                minHeight: 360,
                rowHeight: 42,
                yAxisMaxWidth: 260
            }
        );

        renderChart(
            CHART_KEYS.program,
            SELECTORS.chartJurKuliah,
            buildProgramRows(payload.topJurKuliah),
            {
                color: theme.primary,
                minHeight: 390,
                rowHeight: 44,
                yAxisMaxWidth: 280
            }
        );
    }

    function observeThemeChanges() {
        const observer = new MutationObserver(renderCharts);

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
        renderCharts();
        observeThemeChanges();
    }

    document.addEventListener('DOMContentLoaded', init);
})();