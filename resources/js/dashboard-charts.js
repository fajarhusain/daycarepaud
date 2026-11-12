import * as echarts from 'echarts/core';
import { LineChart, BarChart, PieChart } from 'echarts/charts';
import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    LineChart,
    BarChart,
    PieChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    CanvasRenderer,
]);

function readChartsConfig() {
    const jsonEl = document.getElementById('dashboard-charts-json');
    if (!jsonEl?.textContent) {
        return [];
    }
    try {
        const data = JSON.parse(jsonEl.textContent.trim());
        return Array.isArray(data) ? data : [];
    } catch {
        return [];
    }
}

function disposeDashboardCharts() {
    document.querySelectorAll('[data-echarts-init="1"]').forEach((mount) => {
        const instance = echarts.getInstanceByDom(mount);
        if (instance) {
            instance.dispose();
        }
        delete mount.dataset.echartsInit;
    });
}

function initDashboardCharts() {
    const charts = readChartsConfig();
    if (charts.length === 0) {
        return;
    }

    charts.forEach((chart) => {
        const mount = document.getElementById(chart.id);
        if (!mount) {
            return;
        }

        const existing = echarts.getInstanceByDom(mount);
        if (existing) {
            existing.dispose();
            delete mount.dataset.echartsInit;
        }

        if (mount.dataset.echartsInit === '1') {
            return;
        }
        mount.dataset.echartsInit = '1';

        const height = chart.height || 300;
        mount.style.height = `${height}px`;
        mount.style.width = '100%';

        const instance = echarts.init(mount, null, { renderer: 'canvas' });
        instance.setOption(chart.option || {});
        requestAnimationFrame(() => instance.resize());
    });
}

function resizeDashboardCharts() {
    document.querySelectorAll('[data-echarts-init="1"]').forEach((mount) => {
        echarts.getInstanceByDom(mount)?.resize();
    });
}

if (!window.__dashboardChartsResizeBound) {
    window.__dashboardChartsResizeBound = true;
    window.addEventListener('resize', resizeDashboardCharts, { passive: true });
}

document.addEventListener('turbo:before-cache', disposeDashboardCharts);
document.addEventListener('turbo:load', initDashboardCharts);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
