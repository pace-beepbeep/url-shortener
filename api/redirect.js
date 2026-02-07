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

    // Cari long_url berdasarkan code
    const result = await supabaseRequest('GET', `urls?short_code=eq.${encodeURIComponent(code)}&select=long_url`);

    if (result && result.length > 0) {
        const longUrl = result[0].long_url;
        // Redirect 307 (Temporary) atau 301 (Permanent)
        return Response.redirect(longUrl, 307); 
    } else {
        // Halaman 404 Custom Sederhana
        return new Response(`
            <html>
                <head><title>Link Not Found</title></head>
                <body style="font-family:sans-serif; text-align:center; padding:50px;">
                    <h1>404</h1>
                    <p>Link yang kamu cari tidak ditemukan atau sudah kadaluarsa.</p>
                    <a href="/">Kembali ke Home</a>
                </body>
            </html>
        `, { 
            status: 404,
            headers: { 'Content-Type': 'text/html' }
        });
    }
}