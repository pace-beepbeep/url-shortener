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

    $stmt = $conn->prepare("SELECT short_code from urls WHERE long_url = ?");
    $stmt->bind_param('s', $long_url);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $short_code = $row['short_code'];
    } else {
        // jika belum ada buat link pendek yang baru
        $short_code = buathurufrandom();

        $stmt = $conn->prepare("INSERT INTO urls (long_url, short_code) VALUES (? , ?)");
        $stmt->bind_param('ss', $long_url, $short_code);
        $stmt->execute();
    }

    // Kembalikan Json ke Javascript
    // echo json_encode([
    //     "status" => "success",
    //     "short_url" => "http://localhost/url_shortener/" . $short_code
    // ]);

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']); // Get the subdirectory where the script is running

    // Ensure path doesn't end with a slash (unless it's just /) to avoid double slashes
    $path = rtrim($path, '/\\');

    echo json_encode([
        "status" => "success",
        // Hasilnya jadi: http://localhost/url_shortener/AbC12
        "short_url" => "$protocol://$domain$path/" . $short_code
    ]);
}
