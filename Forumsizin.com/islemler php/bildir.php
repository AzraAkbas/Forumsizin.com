<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Bağlantı hatası']));
}

if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$email = $_SESSION['email'];
$reason = $_POST['reason'] ?? '';
$fotograf_id = $_POST['fotograf_id'] ?? '';

if (empty($reason) || empty($fotograf_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
    exit;
}

$stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
    exit;
}
$kullanici = $result->fetch_assoc();
$kullanici_id = $kullanici['kullanici_id'];

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("INSERT INTO bildirme (neden) VALUES (?)");
    $stmt->bind_param("s", $reason);
    $stmt->execute();

    $bildirme_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO bildirme_islemi (kullanici_id, bildirme_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $kullanici_id, $bildirme_id);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO fotograf_bildirme (fotograf_id, bildirme_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $fotograf_id, $bildirme_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Bildiriniz alınmıştır.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Bildirirken bir hata oluştu.']);
}

$conn->close();
