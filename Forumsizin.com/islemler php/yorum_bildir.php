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
    $yorum_id = $_POST['yorum_id'] ?? null;
    $reason = trim($_POST['reason'] ?? $_POST['reportReason'] ?? '');

    if (!$yorum_id || $reason === '') {
        echo json_encode(["success" => false, "message" => "Eksik bilgi."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Kullanıcı bulunamadı."]);
        exit;
    }

    $user = $result->fetch_assoc();
    $kullanici_id = $user['kullanici_id'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO bildirme (neden) VALUES (?)");
        $stmt->bind_param("s", $reason);
        $stmt->execute();
        $bildirme_id = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO bildirme_islemi (kullanici_id, bildirme_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $kullanici_id, $bildirme_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO yorum_bildirme (yorum_id, bildirme_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $yorum_id, $bildirme_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Yorum bildirimi başarıyla gönderildi."]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Bildirirken bir hata oluştu."]);
    }
}


$conn->close();
?>
