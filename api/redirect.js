import { supabaseRequest } from './supabase.js';

export const config = {
    runtime: 'edge',
};

export default async function handler(req) {
    const url = new URL(req.url);
    const code = url.searchParams.get('code');

    if (!code) {
        return new Response('Missing code', { status: 400 });
    }

    const result = await supabaseRequest('GET', `urls?short_code=eq.${encodeURIComponent(code)}&select=long_url`);

    if (result && result.length > 0) {
        const longUrl = result[0].long_url;
        return Response.redirect(longUrl, 307); // Temporary redirect
    } else {
        return new Response('Link Invalid atau tidak ditemukan.', { status: 404 });
    }
}
