<?php
/**
 * 全局常量配置文件
 * 
 * 定义 AI 权重、彩种区间、VIP 规则、主题色等所有不变的业务常量。
 * 所有数值均为后端核心计算依据，不可随意修改。
 */

// ---------- 彩种号码区间 ----------
define('DLT_FRONT_MIN', 1);
define('DLT_FRONT_MAX', 35);
define('DLT_FRONT_COUNT', 5);

define('DLT_BACK_MIN', 1);
define('DLT_BACK_MAX', 12);
define('DLT_BACK_COUNT', 2);

define('SSQ_RED_MIN', 1);
define('SSQ_RED_MAX', 33);
define('SSQ_RED_COUNT', 6);

define('SSQ_BLUE_MIN', 1);
define('SSQ_BLUE_MAX', 16);
define('SSQ_BLUE_COUNT', 1);

// ---------- AI 预测固定权重 (总和100%) ----------
define('WEIGHT_HOT',          0.20); // 热号权重
define('WEIGHT_COLD',         0.10); // 冷号权重
define('WEIGHT_MISSING',      0.20); // 遗漏权重
define('WEIGHT_CONSECUTIVE',  0.10); // 连号权重
define('WEIGHT_MARKOV',       0.20); // 马尔可夫链权重
define('WEIGHT_MONTE_CARLO',  0.15); // 蒙特卡洛权重
define('WEIGHT_BAYESIAN',     0.05); // 贝叶斯权重

// 确保权重总和为 1.0
assert(abs(WEIGHT_HOT + WEIGHT_COLD + WEIGHT_MISSING + WEIGHT_CONSECUTIVE + WEIGHT_MARKOV + WEIGHT_MONTE_CARLO + WEIGHT_BAYESIAN - 1.0) < 0.001);

// ---------- 蒙特卡洛模拟次数选项 ----------
define('MONTE_CARLO_RUNS', [1000, 10000, 50000, 100000]);

// ---------- 预测策略类型 ----------
define('STRATEGY_STABLE',    '稳健');
define('STRATEGY_BALANCED',  '平衡');
define('STRATEGY_AGGRESSIVE','激进');
define('STRATEGY_EXTREME',   '极限');

// ---------- VIP 规则 ----------
define('VIP_FREE_PREDICT_LIMIT', 3); // 非 VIP 免费单组预测次数上限
define('VIP_MONTHLY_PRICE', 29.9);  // 示例价格（前台展示用）

// ---------- 全局配色主题 ----------
define('THEME_COLORS', [
    'ai_blue'      => '#00F5FF',
    'tech_purple'  => '#6E00FF',
    'neon_green'   => '#00FF9D',
    'alert_red'    => '#FF4D6D',
    'deep_space'   => '#0B1020',
]);

// ---------- 历史开奖期数筛选选项 ----------
define('PERIOD_OPTIONS', [20, 50, 100, 'all']);

// ---------- AI 评级分数段 ----------
define('RATING_THRESHOLD_SSS', 95);
define('RATING_THRESHOLD_SS',  90);
define('RATING_THRESHOLD_S',   80);
define('RATING_THRESHOLD_A',   70);
define('RATING_THRESHOLD_B',   50);

// ---------- 管理员角色标识 ----------
define('ROLE_USER',  'user');
define('ROLE_ADMIN', 'admin');

// ---------- 会话相关常量 ----------
define('SESSION_USER_KEY', 'auth_user');
define('SESSION_ADMIN_KEY', 'is_admin');
