<?php
require 'db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Cari long_url berdasarkan short_code
    // Query setara: SELECT long_url FROM urls WHERE short_code = '$code'
    $result = supabase_request('GET', 'urls?short_code=eq.' . urlencode($code) . '&select=long_url');

    if (!empty($result) && isset($result[0]['long_url'])) {
        $long_url = $result[0]['long_url'];

        // Redirect ke URL asli
        header("Location: " . $long_url);
        exit;
    } else {
        echo "Link Invalid atau tidak ditemukan.";
    }
}
