export const config = {
    runtime: 'edge',
};

// Sebaiknya set process.env.SUPABASE_URL dan process.env.SUPABASE_KEY di Vercel Settings
const SUPABASE_URL = process.env.SUPABASE_URL || 'https://halwnqbgrflakfciprvd.supabase.co';
const SUPABASE_KEY = process.env.SUPABASE_KEY || 'sb_publishable_Ert2jr964ZImgARrcmu5GA_Gbb9MoaU';

export async function supabaseRequest(method, endpoint, data = null) {
    const url = `${SUPABASE_URL}/rest/v1/${endpoint}`;
    
    const headers = {
        'apikey': SUPABASE_KEY,
        'Authorization': `Bearer ${SUPABASE_KEY}`,
        'Content-Type': 'application/json',
        'Prefer': 'return=representation' // Penting agar Supabase mengembalikan data setelah insert
    };

    const options = {
        method,
        headers,
        body: data ? JSON.stringify(data) : null
    };

    try {
        const response = await fetch(url, options);
        // Supabase kadang mengembalikan 204 No Content untuk update/delete tanpa return
        if (response.status === 204) return null;
        
        const json = await response.json();
        return json;
    } catch (e) {
        console.error("Supabase Error:", e);
        return { error: e.message };
    }
}