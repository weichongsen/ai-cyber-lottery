import { PHP, PHPRequestHandler } from '@php-wasm/universal';
import { loadWebRuntime } from '@php-wasm/web';

let handler = null;

async function getHandler(env) {
  if (handler) return handler;
  
  const php = new PHP(await loadWebRuntime('8.5'));
  php.documentRoot = '/';
  
  php.writeFile('/.env', `SUPABASE_DB_HOST=${env.SUPABASE_DB_HOST || ''}
SUPABASE_DB_NAME=${env.SUPABASE_DB_NAME || ''}
SUPABASE_DB_USER=${env.SUPABASE_DB_USER || ''}
SUPABASE_DB_PASS=${env.SUPABASE_DB_PASS || ''}
SUPABASE_DB_PORT=${env.SUPABASE_DB_PORT || '5432'}
`);

  handler = new PHPRequestHandler({ phpFactory: async () => php });
  return handler;
}

export default {
  async fetch(request, env, ctx) {
    try {
      const h = await getHandler(env);
      return await h.request(request);
    } catch (e) {
      return new Response('Server Error: ' + e.message, { status: 500 });
    }
  }
};
