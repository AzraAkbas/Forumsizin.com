<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmanız gerekiyor.']);
    exit;
}

if (!isset($_POST['fotograf_id'])) {
    echo json_encode(['success' => false, 'message' => 'Fotoğraf ID eksik.']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantı hatası: ' . $conn->connect_error]);
    exit;
}

$session_email = $_SESSION['email'];
$fotograf_id = intval($_POST['fotograf_id']);

$queryUser = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
if (!$queryUser) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı sorgusu hazırlanamadı: ' . $conn->error]);
    exit;
}
$queryUser->bind_param("s", $session_email);
$queryUser->execute();
$resultUser = $queryUser->get_result();

if ($resultUser->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı.']);
    exit;
}
$userRow = $resultUser->fetch_assoc();
$kullanici_id = intval($userRow['kullanici_id']);
$queryUser->close();

$query = "
    SELECT k.kaydetme_id
    FROM kaydetme k
    JOIN kullanici_kaydetme kk ON k.kaydetme_id = kk.kaydetme_id
    JOIN fotograf_kaydetme fk ON k.kaydetme_id = fk.kaydetme_id
    WHERE kk.kullanici_id = ? AND fk.fotograf_id = ?
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Kontrol sorgusu hazırlanamadı: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $kullanici_id, $fotograf_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($kaydetme_id);
    $stmt->fetch();
    $stmt->close();

    $del1 = $conn->prepare("DELETE FROM kullanici_kaydetme WHERE kaydetme_id = ?");
    $del1->bind_param("i", $kaydetme_id);
    $del1->execute();
    $del1->close();

    $del2 = $conn->prepare("DELETE FROM fotograf_kaydetme WHERE kaydetme_id = ?");
    $del2->bind_param("i", $kaydetme_id);
    $del2->execute();
    $del2->close();

    $del3 = $conn->prepare("DELETE FROM kaydetme WHERE kaydetme_id = ?");
    $del3->bind_param("i", $kaydetme_id);
    $del3->execute();
    $del3->close();

    echo json_encode(['success' => true, 'message' => 'Fotoğraf kayıttan kaldırıldı.']);
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO kaydetme (kaydetme_tarihi) VALUES (CURRENT_TIMESTAMP)");
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Kaydetme tablosuna ekleme hatası: ' . $stmt->error]);
    exit;
}
$kaydetme_id = $conn->insert_id;
$stmt->close();

$stmt = $conn->prepare("INSERT INTO kullanici_kaydetme (kullanici_id, kaydetme_id) VALUES (?, ?)");
$stmt->bind_param("ii", $kullanici_id, $kaydetme_id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'kullanici_kaydetme ekleme hatası: ' . $stmt->error]);
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO fotograf_kaydetme (fotograf_id, kaydetme_id) VALUES (?, ?)");
$stmt->bind_param("ii", $fotograf_id, $kaydetme_id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'fotograf_kaydetme ekleme hatası: ' . $stmt->error]);
    exit;
}
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Fotoğraf kaydedildi.']);
$conn->close();
