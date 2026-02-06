<?php
define('SUPABASE_URL', 'https://halwnqbgrflakfciprvd.supabase.co');
define('SUPABASE_KEY', 'sb_publishable_Ert2jr964ZImgARrcmu5GA_Gbb9MoaU');

function supabase_request($method, $endpoint, $data = null)
{
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation' // Agar Supabase mengembalikan data setelah insert
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => $error];
    }

    return json_decode($response, true);
}
