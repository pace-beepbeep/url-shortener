<?php
require 'db.php';

// fungsi huruf random
function buathurufrandom($length = 5)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $long_url = $_POST['url'];

    //  Validasi link 
    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
        echo json_encode(["status" => "error", "message" => "Url Tidak Valid : "]);
        exit;
    }

    $existing = supabase_request('GET', 'urls?long_url=eq.' . urlencode($long_url) . '&select=short_code');
    if (!empty($existing) && isset($existing[0]['short_code'])) {
        $short_code = $existing[0]['short_code'];
    } else {
        // 2. Jika belum ada, buat kode baru dan simpan
        $short_code = buathurufrandom();

        $data = [
            'long_url' => $long_url,
            'short_code' => $short_code
        ];

        // Query setara: INSERT INTO urls ...
        $insert = supabase_request('POST', 'urls', $data);

        // Cek jika ada error saat insert
        if (isset($insert['error']) || isset($insert['message'])) {
            // Opsional: Handle jika kode random ternyata duplikat (jarang terjadi)
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database."]);
            exit;
        }
    }

    // Persiapan output URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $path = rtrim($path, '/\\');

    echo json_encode([
        "status" => "success",
        "short_url" => "$protocol://$domain$path/" . $short_code
    ]);
}
