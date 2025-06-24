<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Veritabanı bağlantı hatası']));
}

$sql = "SELECT emoji_id, simge FROM emoji";
$result = $conn->query($sql);

$emojiler = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $emojiler[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($emojiler);
$conn->close();
?>
