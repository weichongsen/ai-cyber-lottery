import { PhpWeb } from '@php-wasm/web';

let php;

async function getPhp() {
  if (!php) {
    php = await PhpWeb.load('8.2', {
      // php.wasm 文件放在项目根目录
      locateFile: (file) => {
        if (file === 'php.wasm') {
          return '/php.wasm';
        }
        return file;
      }
    });
    // 设置文档根目录
    php.documentRoot = '/';
  }
  return php;
}

export default {
  async fetch(request, env, ctx) {
    try {
      const phpInstance = await getPhp();

      // 将 Cloudflare 环境变量注入 PHP $_SERVER
      const envVars = {
        SUPABASE_DB_HOST: env.SUPABASE_DB_HOST || '',
        SUPABASE_DB_NAME: env.SUPABASE_DB_NAME || '',
        SUPABASE_DB_USER: env.SUPABASE_DB_USER || '',
        SUPABASE_DB_PASS: env.SUPABASE_DB_PASS || '',
        SUPABASE_DB_PORT: env.SUPABASE_DB_PORT || '5432'
      };
      for (const [key, value] of Object.entries(envVars)) {
        if (value) {
          request.headers.set('X-Env-' + key, value);
        }
      }

      // 让 PHP-WASM 处理这个请求
      const response = await phpInstance.request(request, {
        env: envVars,
        documentRoot: '/',
        staticFilePatterns: [
          /\.css$/,
          /\.js$/,
          /\.png$/,
          /\.jpg$/,
          /\.svg$/,
          /\.woff2$/,
          /\.mp3$/,
          /\.wav$/
        ]
      });
      return response;
    } catch (error) {
      return new Response('服务器内部错误: ' + error.message, { status: 500 });
    }
  }
};