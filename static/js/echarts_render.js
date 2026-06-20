/**
 * ECharts 图表渲染函数集
 * 为图表中心页面提供热号、冷号、走势、奇偶图表绘制。
 * 页面需引入 ECharts 库，并存在相应的 DOM 容器。
 */

/**
 * 刷新所有图表（由页面事件触发）
 */
async function refreshCharts() {
    const lotteryType = document.getElementById('chart-lottery-type')?.value || 'dlt';
    const period = document.getElementById('chart-period')?.value || '50';

    try {
        const resp1 = await fetch(`/api/api_predict.php?action=hot_cold_chart&lottery_type=${lotteryType}&period=${period}`);
        const hotColdData = await resp1.json();

        const resp2 = await fetch(`/api/api_predict.php?action=trend_chart&lottery_type=${lotteryType}&period=${period}`);
        const trendData = await resp2.json();

        if (hotColdData.success && trendData.success) {
            renderChartHot(hotColdData.data);
            renderChartCold(hotColdData.data);
            renderChartTrend(trendData.data);
            renderChartOddEven(trendData.data);
        }
    } catch (e) {
        console.error('图表数据加载失败', e);
    }
}

function renderChartHot(data) {
    const chartDom = document.getElementById('chart-hot');
    if (!chartDom) return;
    const myChart = echarts.init(chartDom, 'dark');
    const mainKeys = data.main_range;
    const mainValues = mainKeys.map(k => data.main_freq[k] || 0);
    const option = {
        title: { text: '热号频次统计', textStyle: { color: '#00F5FF' } },
        tooltip: { trigger: 'axis' },
        xAxis: { data: mainKeys, axisLabel: { color: '#00FF9D' } },
        yAxis: { axisLabel: { color: '#00FF9D' } },
        series: [{ data: mainValues, type: 'bar', itemStyle: { color: '#6E00FF' } }]
    };
    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

function renderChartCold(data) {
    const chartDom = document.getElementById('chart-cold');
    if (!chartDom) return;
    const myChart = echarts.init(chartDom, 'dark');
    const mainKeys = data.main_range;
    const mainValues = mainKeys.map(k => data.main_freq[k] || 0);
    const option = {
        title: { text: '冷号频次统计', textStyle: { color: '#FF4D6D' } },
        tooltip: { trigger: 'axis' },
        xAxis: { data: mainKeys, axisLabel: { color: '#00FF9D' } },
        yAxis: { axisLabel: { color: '#00FF9D' } },
        series: [{ data: mainValues, type: 'bar', itemStyle: { color: '#FF4D6D' } }]
    };
    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

function renderChartTrend(data) {
    const chartDom = document.getElementById('chart-trend-sum');
    if (!chartDom) return;
    const myChart = echarts.init(chartDom, 'dark');
    const option = {
        title: { text: '和值走势', textStyle: { color: '#00F5FF' } },
        tooltip: { trigger: 'axis' },
        xAxis: { data: data.dates, axisLabel: { rotate: 30, color: '#00FF9D' } },
        yAxis: { axisLabel: { color: '#00FF9D' } },
        series: [{ data: data.sums, type: 'line', smooth: true, lineStyle: { color: '#00F5FF' }, itemStyle: { color: '#00F5FF' } }]
    };
    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

function renderChartOddEven(data) {
    const chartDom = document.getElementById('chart-odd-even');
    if (!chartDom) return;
    const myChart = echarts.init(chartDom, 'dark');
    const option = {
        title: { text: '奇偶比例趋势', textStyle: { color: '#00F5FF' } },
        tooltip: { trigger: 'axis' },
        xAxis: { data: data.dates, axisLabel: { rotate: 30, color: '#00FF9D' } },
        yAxis: { max: 100, axisLabel: { color: '#00FF9D' } },
        series: [
            { name: '奇数%', data: data.odd_ratios, type: 'line', smooth: true, lineStyle: { color: '#6E00FF' } },
            { name: '大数%', data: data.big_ratios, type: 'line', smooth: true, lineStyle: { color: '#00FF9D' } }
        ]
    };
    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}