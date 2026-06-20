-- Supabase PostgreSQL 完整建表脚本
-- 适配 PHP PDO (pgsql) 连接，使用 jsonb 存储号码数据

-- 用户表
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('user','admin')),
    is_vip BOOLEAN DEFAULT FALSE,
    vip_expire_date TIMESTAMPTZ,
    free_predictions_used INT DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 开奖数据表（统一存储大乐透与双色球）
CREATE TABLE lottery_draws (
    id SERIAL PRIMARY KEY,
    lottery_type VARCHAR(3) NOT NULL CHECK (lottery_type IN ('dlt','ssq')),
    draw_num VARCHAR(20) NOT NULL,
    draw_date DATE NOT NULL,
    numbers JSONB NOT NULL,        -- 前区/红球，例如 [1,5,12,23,35] 或 [3,7,11,18,25,33]
    special_numbers JSONB NOT NULL,-- 后区/蓝球，例如 [2,9] 或 [6]
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(lottery_type, draw_num)
);

-- 收藏表
CREATE TABLE collections (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    lottery_type VARCHAR(3) NOT NULL,
    numbers JSONB NOT NULL,        -- 主号码
    special_numbers JSONB NOT NULL,-- 特殊号码
    note TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 预测记录表（AI 预测结果存档）
CREATE TABLE predict_logs (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    lottery_type VARCHAR(3) NOT NULL,
    predict_type VARCHAR(20) NOT NULL, -- 'single','multi_5','multi_10','lab'
    strategy VARCHAR(20),              -- 策略类型（稳健/平衡/激进/极限）
    numbers JSONB NOT NULL,            -- 预测的号码组合列表
    special_numbers JSONB NOT NULL,
    ai_score DECIMAL(5,2),
    rating VARCHAR(5),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- VIP 开通/取消记录表（管理员操作日志）
CREATE TABLE vip_records (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    action VARCHAR(10) NOT NULL CHECK (action IN ('activate','cancel')),
    operated_by INT REFERENCES users(id), -- 操作管理员ID
    reason TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 系统配置表（主题、开关等持久化）
CREATE TABLE system_config (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    config_key VARCHAR(50) NOT NULL,
    config_value TEXT NOT NULL,
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, config_key)
);

-- 音效文件管理表（管理员后台使用）
CREATE TABLE audio_files (
    id SERIAL PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_by INT REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 创建索引以加速查询
CREATE INDEX idx_lottery_type_date ON lottery_draws(lottery_type, draw_date);
CREATE INDEX idx_collections_user ON collections(user_id, lottery_type);
CREATE INDEX idx_predict_user_time ON predict_logs(user_id, created_at DESC);
CREATE INDEX idx_config_user ON system_config(user_id);