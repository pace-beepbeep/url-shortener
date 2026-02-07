import { supabaseRequest } from './supabase.js';

export const config = {
    runtime: 'edge',
};

function generateRandomString(length = 6) {
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
        const body = await req.json();
        const url = body.url;

        // Validasi URL
        if (!url) {
            return new Response(JSON.stringify({ status: 'error', message: 'URL is required' }), { status: 400 });
        }
        try {
            new URL(url); // Cek format URL valid
        } catch (_) {
             return new Response(JSON.stringify({ status: 'error', message: 'URL tidak valid. Pastikan pakai http:// atau https://' }), { status: 400 });
        }

        // 1. Cek apakah URL sudah pernah dipendekkan sebelumnya
        const existing = await supabaseRequest('GET', `urls?long_url=eq.${encodeURIComponent(url)}&select=short_code`);
        
        let shortCode;
        if (existing && existing.length > 0) {
            shortCode = existing[0].short_code;
        } else {
            // 2. Buat kode baru jika belum ada
            shortCode = generateRandomString();
            const data = { long_url: url, short_code: shortCode };
            const insert = await supabaseRequest('POST', 'urls', data);
            
            if (insert.error) {
                 return new Response(JSON.stringify({ status: 'error', message: 'Database Error' }), { status: 500 });
            }
        }

        // Konstruksi Short URL
        const protocol = req.headers.get('x-forwarded-proto') || 'https';
        const host = req.headers.get('host');
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
        return new Response(JSON.stringify({ status: 'error', message: 'Internal Server Error: ' + error.message }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' },
        });
    }
}