(function () {
    'use strict';

    const SCORE_FIELD_IDS = [
        'f-pai',
        'f-ppkn',
        'f-bind',
        'f-mtk',
        'f-bing',
        'f-ukk'
    ];

    const STAT_ELEMENTS = {
        mean: 'stat-mean',
        max: 'stat-max',
        min: 'stat-min',
        std: 'stat-std'
    };

    const BAR_ELEMENTS = {
        mean: 'bar-mean',
        max: 'bar-max',
        min: 'bar-min',
        std: 'bar-std'
    };

    const EMPTY_VALUE = '—';
    const MIN_SCORE = 0;
    const MAX_SCORE = 100;
    const STD_BAR_MULTIPLIER = 5;

    function getElement(id) {
        return document.getElementById(id);
    }

    function getScoreInputs() {
        return SCORE_FIELD_IDS
            .map(getElement)
            .filter(Boolean);
    }

    function getNumericValues(inputs) {
        return inputs
            .map((input) => Number.parseFloat(input.value))
            .filter((value) => Number.isFinite(value));
    }

    function clampScoreInput(input) {
        const value = Number.parseFloat(input.value);

        if (!Number.isFinite(value)) {
            return;
        }

        if (value > MAX_SCORE) {
            input.value = MAX_SCORE;
            return;
        }

        if (value < MIN_SCORE) {
            input.value = MIN_SCORE;
        }
    }

    function calculateStats(values) {
        const total = values.reduce((sum, value) => sum + value, 0);
        const mean = total / values.length;
        const max = Math.max(...values);
        const min = Math.min(...values);
        const variance = values.reduce((sum, value) => {
            return sum + Math.pow(value - mean, 2);
        }, 0) / values.length;

        return {
            mean,
            max,
            min,
            std: Math.sqrt(variance)
        };
    }

    function setText(id, value) {
        const element = getElement(id);

        if (element) {
            element.textContent = value;
        }
    }

    function setBarWidth(id, value) {
        const element = getElement(id);

        if (element) {
            element.style.width = `${Math.min(Math.max(value, 0), 100)}%`;
        }
    }

    function resetStats() {
        Object.values(STAT_ELEMENTS).forEach((id) => setText(id, EMPTY_VALUE));
        Object.values(BAR_ELEMENTS).forEach((id) => setBarWidth(id, 0));
    }

    function renderStats(stats) {
        setText(STAT_ELEMENTS.mean, stats.mean.toFixed(1));
        setText(STAT_ELEMENTS.max, stats.max.toFixed(0));
        setText(STAT_ELEMENTS.min, stats.min.toFixed(0));
        setText(STAT_ELEMENTS.std, stats.std.toFixed(2));

        setBarWidth(BAR_ELEMENTS.mean, stats.mean);
        setBarWidth(BAR_ELEMENTS.max, stats.max);
        setBarWidth(BAR_ELEMENTS.min, stats.min);
        setBarWidth(BAR_ELEMENTS.std, stats.std * STD_BAR_MULTIPLIER);
    }

    function updateStats(inputs) {
        const values = getNumericValues(inputs);

        if (!values.length) {
            resetStats();
            return;
        }

        renderStats(calculateStats(values));
    }

    function initScoreSummary() {
        const form = getElement('formPrediksi');
        const scoreInputs = getScoreInputs();

        if (!form || !scoreInputs.length) {
            return;
        }

        form.addEventListener('input', function (event) {
            const target = event.target;

            if (!target.classList.contains('score-input')) {
                return;
            }

            clampScoreInput(target);
            updateStats(scoreInputs);
        });

        updateStats(scoreInputs);
    }

    document.addEventListener('DOMContentLoaded', initScoreSummary);
})();