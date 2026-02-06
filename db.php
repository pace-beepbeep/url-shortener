<?php
$host = 'localhost';
$db = 'url_short';
$user = 'root';
$password = '';

$conn = mysqli_connect($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Koneksi Gagal : " . $conn->connect_error);
}
?>