<!-- ECharts 图表可视化中心 -->
<div class="page-container" id="chart-page">
    <h2 class="page-title">📊 AI 全维度分析图表</h2>
    <div class="chart-controls cyber-panel">
        <div class="control-group">
            <label>彩种</label>
            <select id="chart-lottery-type" class="cyber-select" onchange="refreshCharts()">
                <option value="dlt">大乐透</option>
                <option value="ssq">双色球</option>
            </select>
        </div>
        <div class="control-group">
            <label>分析期数</label>
            <select id="chart-period" class="cyber-select" onchange="refreshCharts()">
                <option value="20">近20期</option>
                <option value="50" selected>近50期</option>
                <option value="100">近100期</option>
                <option value="all">全部历史</option>
            </select>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-container cyber-glass" id="chart-hot" style="height:400px;"></div>
        <div class="chart-container cyber-glass" id="chart-cold" style="height:400px;"></div>
        <div class="chart-container cyber-glass" id="chart-trend-sum" style="height:400px;"></div>
        <div class="chart-container cyber-glass" id="chart-odd-even" style="height:400px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    refreshCharts();
});

async function refreshCharts() {
    const lotteryType = document.getElementById('chart-lottery-type').value;
    const period = document.getElementById('chart-period').value;

    try {
        // 获取热号冷号数据
        const resp1 = await fetch(`/api/api_predict.php?action=hot_cold_chart&lottery_type=${lotteryType}&period=${period}`);
        const hotColdData = await resp1.json();

        // 获取趋势数据
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
</script>