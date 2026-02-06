<?php
// --- AREA DEBUGGING (Hapus nanti saat production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ----------------------------------------------------

require 'db.php';

// Cek apakah ekstensi cURL aktif
if (!function_exists('curl_init')) {
    echo json_encode(["status" => "error", "message" => "Fatal Error: Ekstensi PHP cURL belum diaktifkan di server ini."]);
    exit;
}

// Fungsi Helper
function buathurufrandom($length = 5)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

// Handle CORS (Opsional, jaga-jaga jika beda port)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Pastikan header selalu JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $long_url = '';

    // 1. Baca input JSON
    $json_input = file_get_contents('php://input');
    $decoded = json_decode($json_input, true);

    if (is_array($decoded) && isset($decoded['url'])) {
        $long_url = $decoded['url'];
    } elseif (isset($_POST['url'])) {
        $long_url = $_POST['url'];
    }

    if (empty($long_url) || !filter_var($long_url, FILTER_VALIDATE_URL)) {
        echo json_encode(["status" => "error", "message" => "URL tidak valid atau kosong."]);
        exit;
    }

    // 2. Cek database Supabase
    $existing = supabase_request('GET', 'urls?long_url=eq.' . urlencode($long_url) . '&select=short_code');

    if (!empty($existing) && isset($existing[0]['short_code'])) {
        $short_code = $existing[0]['short_code'];
    } else {
        $short_code = buathurufrandom();
        $data = ['long_url' => $long_url, 'short_code' => $short_code];

        $insert = supabase_request('POST', 'urls', $data);

        if (isset($insert['error'])) {
            // Debugging: Tampilkan pesan error asli dari Supabase jika ada
            $errMsg = is_array($insert['error']) ? json_encode($insert['error']) : "Gagal menyimpan ke database.";
            echo json_encode(["status" => "error", "message" => $errMsg]);
            exit;
        }
    }

    // 3. Output Sukses
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $path = rtrim($path, '/\\');

    echo json_encode([
        "status" => "success",
        "short_url" => "$protocol://$domain$path/" . $short_code
    ]);
} else {
    // Jika request bukan POST (misal GET atau OPTIONS), beritahu user
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed. Request yang diterima server adalah: " . $_SERVER['REQUEST_METHOD']
    ]);
}
