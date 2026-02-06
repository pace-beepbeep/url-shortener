<?php
require 'db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // untuk redirect
        header("Location: " . $row['long_url']);
        exit;
    } else {
        echo "Link Invalid";
    }
}
