<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmanız gerekiyor.']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantı hatası.']);
    exit;
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı.']);
    exit;
}
$kullanici_id = $result->fetch_assoc()['kullanici_id'];
$stmt->close();

$query = "
    SELECT fk.fotograf_id 
    FROM kaydetme k
    JOIN kullanici_kaydetme kk ON k.kaydetme_id = kk.kaydetme_id
    JOIN fotograf_kaydetme fk ON k.kaydetme_id = fk.kaydetme_id
    WHERE kk.kullanici_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();

$fotograf_ids = [];
while ($row = $result->fetch_assoc()) {
    $fotograf_ids[] = $row['fotograf_id'];
}

echo json_encode(['success' => true, 'kaydedilenler' => $fotograf_ids]);
$conn->close();
?>
