<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$giris_yapan_mail = $_SESSION['email'] ?? null;

if (!$giris_yapan_mail) {
    echo "giris_yok";
    exit;
}

$stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
$stmt->bind_param("s", $giris_yapan_mail);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "kullanici_bulunamadi";
    exit;
}

$giris_yapan = $res->fetch_assoc();
$giris_yapan_id = $giris_yapan['kullanici_id'];


$takip_edilecek_id = $_POST['takip_edilecek_id'] ?? null;

if (!$takip_edilecek_id || !is_numeric($takip_edilecek_id)) {
    echo "parametre_yok";
    exit;
}

if ($takip_edilecek_id == $giris_yapan_id) {
    echo "kendi_kendini_takip_edemez";
    exit;
}

$stmt = $conn->prepare("SELECT 1 FROM takiplesme WHERE takipci_id = ? AND takip_id = ? LIMIT 1");
$stmt->bind_param("ii", $giris_yapan_id, $takip_edilecek_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM takiplesme WHERE takipci_id = ? AND takip_id = ?");
    $stmt->bind_param("ii", $giris_yapan_id, $takip_edilecek_id);
    if ($stmt->execute()) {
        echo "takipten_cikildi";
    } else {
        echo "hata";
    }
} else {
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO takiplesme (takipci_id, takip_id, takip_tarihi) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $giris_yapan_id, $takip_edilecek_id);

    if ($stmt->execute()) {
        echo "takip_edildi";
    } else {
        echo "hata";
    }
}

$stmt->close();
$conn->close();
?>
