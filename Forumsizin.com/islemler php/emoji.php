<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Veritabanı bağlantısı başarısız."]));
}

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "Giriş yapılmamış."]);
    exit;
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Kullanıcı bulunamadı."]);
    exit;
}
$row = $result->fetch_assoc();
$kullanici_id = $row['kullanici_id'];

$fotograf_id = $_POST['fotograf_id'];
$emoji_id = $_POST['emoji_id'];


$stmt = $conn->prepare("SELECT * FROM emoji_birakilma WHERE kullanici_id = ? AND fotograf_id = ?");
$stmt->bind_param("ii", $kullanici_id, $fotograf_id);
$stmt->execute();
$checkResult = $stmt->get_result();

if ($checkResult->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE emoji_birakilma SET emoji_id = ? WHERE kullanici_id = ? AND fotograf_id = ?");
    $stmt->bind_param("iii", $emoji_id, $kullanici_id, $fotograf_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Emoji güncellendi."]);
    } else {
        echo json_encode(["success" => false, "message" => "Emoji güncellenemedi."]);
    }
} else {

    $stmt = $conn->prepare("INSERT INTO emoji_birakilma (kullanici_id, fotograf_id, emoji_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $kullanici_id, $fotograf_id, $emoji_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Emoji eklendi."]);
    } else {
        echo json_encode(["success" => false, "message" => "Emoji eklenemedi."]);
    }
}

$conn->close();
?>
