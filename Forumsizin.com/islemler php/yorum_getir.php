<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Veritabanı bağlantısı başarısız"]);
    exit;
}

if (!isset($_GET['fotograf_id']) || !is_numeric($_GET['fotograf_id'])) {
    echo json_encode(["success" => false, "message" => "Geçerli Fotograf ID gerekli"]);
    exit;
}

$fotograf_id = intval($_GET['fotograf_id']);

$stmt = $conn->prepare("
    SELECT 
        y.yorum_id,
        y.yorum_icerik, 
        k.kullanici_adi, 
        y.yazilma_tarihi AS tarih
    FROM yorum y
    JOIN kullanici_yorum ky ON y.yorum_id = ky.yorum_id
    JOIN kullanici k ON ky.kullanici_id = k.kullanici_id
    JOIN fotograf_yorum fy ON y.yorum_id = fy.yorum_id
    WHERE fy.fotograf_id = ?
    ORDER BY y.yazilma_tarihi DESC
");

$stmt->bind_param("i", $fotograf_id);
$stmt->execute();
$result = $stmt->get_result();

$yorumlar = [];

while ($row = $result->fetch_assoc()) {
    $yorumlar[] = [
        "yorum_id" => $row['yorum_id'],
        "yorum_icerik" => $row['yorum_icerik'],
        "kullanici_adi" => $row['kullanici_adi'],
        "tarih" => $row['tarih']
    ];
}

echo json_encode([
    "success" => true,
    "yorumlar" => $yorumlar
]);

$conn->close();
?>
