<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'dev_sanggar');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$result = $conn->query('SELECT id_user, kode_barcode, id_role FROM users LIMIT 5;');
while($row = $result->fetch_assoc()) {
    echo 'User ID: ' . $row['id_user'] . ' | Barcode: ' . $row['kode_barcode'] . ' | Role ID: ' . $row['id_role'] . PHP_EOL;
}
$conn->close();
?>
