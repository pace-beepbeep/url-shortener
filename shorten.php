<?php
require 'db.php';

// Fungsi Helper: Membuat kode acak
function buathurufrandom($length = 5)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $long_url = '';

    // 1. Coba baca dari JSON (ini yang dikirim oleh index.html baru)
    $json_input = file_get_contents('php://input');
    $decoded = json_decode($json_input, true);

    if (is_array($decoded) && isset($decoded['url'])) {
        $long_url = $decoded['url'];
    }
    // 2. Fallback: Coba baca dari Form Data biasa (jika pakai form submit biasa)
    elseif (isset($_POST['url'])) {
        $long_url = $_POST['url'];
    }

    // Validasi URL
    if (empty($long_url) || !filter_var($long_url, FILTER_VALIDATE_URL)) {
        // Mengembalikan JSON error agar bisa ditangkap frontend
        echo json_encode(["status" => "error", "message" => "URL tidak valid atau kosong."]);
        exit;
    }

    // Cek apakah URL sudah ada di Supabase
    $existing = supabase_request('GET', 'urls?long_url=eq.' . urlencode($long_url) . '&select=short_code');

    if (!empty($existing) && isset($existing[0]['short_code'])) {
        $short_code = $existing[0]['short_code'];
    } else {
        // Jika belum ada, buat baru
        $short_code = buathurufrandom();

        $data = [
            'long_url' => $long_url,
            'short_code' => $short_code
        ];

        $insert = supabase_request('POST', 'urls', $data);

        // Cek error dari Supabase
        if (isset($insert['error'])) {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database."]);
            exit;
        }
    }

    // Susun URL hasil
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];

    // Hapus nama file script dari path agar bersih
    $path = dirname($_SERVER['PHP_SELF']);
    $path = rtrim($path, '/\\');

    echo json_encode([
        "status" => "success",
        "short_url" => "$protocol://$domain$path/" . $short_code
    ]);
}
