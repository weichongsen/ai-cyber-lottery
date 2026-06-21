<?php
/**
 * Supabase PostgreSQL 数据库连接配置
 * 
 * ⚠️ 安全要求：
 * 所有数据库连接信息通过 Cloudflare 环境变量注入，严禁硬编码密钥。
 * 在 Cloudflare Pages 部署时，需在 Dashboard → Settings → Environment Variables 添加：
 *   SUPABASE_DB_HOST    (例如：db.xxxxxxxxxxxx.supabase.co)
 *   SUPABASE_DB_NAME    (例如：postgres)
 *   SUPABASE_DB_USER    (例如：postgres)
 *   SUPABASE_DB_PASS    (数据库密码)
 *   SUPABASE_DB_PORT    (默认：5432，可选)
 * 
 * 本地开发请复制 .env.example 为 .env 并填写真实值，
 * 同时确保 .gitignore 已忽略 .env 文件。
 */

// 读取环境变量（兼容 Cloudflare Workers 注入的 $_SERVER 及本地 .env）
$db_host = getenv('SUPABASE_DB_HOST') ?: ($_SERVER['SUPABASE_DB_HOST'] ?? 'localhost');
$db_name = getenv('SUPABASE_DB_NAME') ?: ($_SERVER['SUPABASE_DB_NAME'] ?? 'postgres');
$db_user = getenv('SUPABASE_DB_USER') ?: ($_SERVER['SUPABASE_DB_USER'] ?? 'postgres');
$db_pass = getenv('SUPABASE_DB_PASS') ?: ($_SERVER['SUPABASE_DB_PASS'] ?? '');
$db_port = getenv('SUPABASE_DB_PORT') ?: ($_SERVER['SUPABASE_DB_PORT'] ?? '5432');

// 构造 PDO DSN
$dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name};sslmode=require";

/**
 * 获取数据库连接 (PDO 单例)
 * 
 * @return PDO
 * @throws PDOException 连接失败时抛出异常
 */
function getDBConnection(): PDO {
    global $dsn, $db_user, $db_pass;
    
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // 设置会话时区，避免时间不一致
        $pdo->exec("SET TIME ZONE 'UTC'");
        return $pdo;
    } catch (PDOException $e) {
        // 仅记录日志，不向前端暴露详细错误
        error_log('Database connection failed: ' . $e->getMessage());
        throw new PDOException('数据库连接失败，请联系管理员。');
    }
}