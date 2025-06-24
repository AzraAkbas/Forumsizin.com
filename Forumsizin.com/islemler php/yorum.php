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

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "Giriş yapmalısınız."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_SESSION['email'];
    $fotograf_id = $_POST['fotograf_id'] ?? null;
    $yorum = trim($_POST['yorum'] ?? '');

    if (!$fotograf_id || $yorum === '') {
        echo json_encode(["success" => false, "message" => "Eksik bilgi."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Kullanıcı bulunamadı."]);
        exit;
    }

    $user = $userResult->fetch_assoc();
    $kullanici_id = $user['kullanici_id'];

    $stmt = $conn->prepare("INSERT INTO yorum (yorum_icerik) VALUES (?)");
    $stmt->bind_param("s", $yorum);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Yorum eklenemedi."]);
        exit;
    }


    $yorum_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO kullanici_yorum (kullanici_id, yorum_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $kullanici_id, $yorum_id);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Kullanıcı-yorum ilişkisi eklenemedi."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO fotograf_yorum (fotograf_id, yorum_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $fotograf_id, $yorum_id);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Fotoğraf-yorum ilişkisi eklenemedi."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Yorum başarıyla eklendi."]);

} else {
    echo json_encode(["success" => false, "message" => "Geçersiz istek."]);
}

$conn->close();

?>
