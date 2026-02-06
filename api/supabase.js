export const config = {
    runtime: 'edge', // Optional: Use edge runtime for speed
};

const SUPABASE_URL = process.env.SUPABASE_URL || 'https://halwnqbgrflakfciprvd.supabase.co';
const SUPABASE_KEY = process.env.SUPABASE_KEY || 'sb_publishable_Ert2jr964ZImgARrcmu5GA_Gbb9MoaU';

export async function supabaseRequest(method, endpoint, data = null) {
    const url = `${SUPABASE_URL}/rest/v1/${endpoint}`;
    const headers = {
        'apikey': SUPABASE_KEY,
        'Authorization': `Bearer ${SUPABASE_KEY}`,
        'Content-Type': 'application/json',
        'Prefer': 'return=representation'
    };

    const options = {
        method,
        headers,
        body: data ? JSON.stringify(data) : null
    };

    const response = await fetch(url, options);
    const json = await response.json();
    return json;
}
