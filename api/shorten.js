import { supabaseRequest } from './supabase.js';

export const config = {
    runtime: 'edge',
};

function generateRandomString(length = 5) {
    const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

export default async function handler(req) {
    // CORS Handling
    if (req.method === 'OPTIONS') {
        return new Response(null, {
            status: 200,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Methods': 'POST, OPTIONS',
                'Access-Control-Allow-Headers': 'Content-Type',
            },
        });
    }

    if (req.method !== 'POST') {
        return new Response(JSON.stringify({ message: 'Method not allowed' }), {
            status: 405,
            headers: { 'Content-Type': 'application/json' },
        });
    }

    try {
        const { url } = await req.json();

        if (!url) {
            return new Response(JSON.stringify({ status: 'error', message: 'URL is required' }), {
                status: 400,
                headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
            });
        }

        try {
            new URL(url); // Validate URL
        } catch (_) {
             return new Response(JSON.stringify({ status: 'error', message: 'Url Tidak Valid :' }), {
                status: 400,
                headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
            });
        }

        // Check for existing URL
        const existing = await supabaseRequest('GET', `urls?long_url=eq.${encodeURIComponent(url)}&select=short_code`);
        
        let shortCode;
        if (existing && existing.length > 0) {
            shortCode = existing[0].short_code;
        } else {
            // Create new
            shortCode = generateRandomString();
            const data = { long_url: url, short_code: shortCode };
            const insert = await supabaseRequest('POST', 'urls', data);
            
            if (insert.error) {
                 return new Response(JSON.stringify({ status: 'error', message: 'Gagal menyimpan ke database.' }), {
                    status: 500,
                    headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
                });
            }
        }

        const protocol = req.headers.get('x-forwarded-proto') || 'https';
        const host = req.headers.get('host');
        // Since we are running in an API route /api/shorten, and the short link should be at root /, we just use origin + code
        const shortUrl = `${protocol}://${host}/${shortCode}`;

        return new Response(JSON.stringify({
            status: 'success',
            short_url: shortUrl
        }), {
            status: 200,
            headers: { 
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*'
            },
        });

    } catch (error) {
        return new Response(JSON.stringify({ status: 'error', message: 'Internal Server Error' }), {
            status: 500,
            headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
        });
    }
}
