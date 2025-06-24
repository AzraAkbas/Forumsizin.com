<?php
session_start();

$host = "localhost";
$db = "forumsizin";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

if (!isset($_SESSION['email'])) {
    die("Giriş yapmalısınız.");
}

$email = $_SESSION['email'];

$sql = "SELECT kullanici_id FROM kullanici WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Kullanıcı bulunamadı.");
}

$row = $result->fetch_assoc();
$aktifKullaniciId = (int)$row['kullanici_id'];

$chatKullanici = isset($_GET['chatWith']) ? (int)$_GET['chatWith'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['mesaj'])) && $chatKullanici) {

    $sqlTakipKontrol = "
        SELECT COUNT(*) AS cnt
        FROM takiplesme t1
        INNER JOIN takiplesme t2 ON t1.takipci_id = t2.takip_id AND t1.takip_id = t2.takipci_id
        WHERE t1.takipci_id = ? AND t1.takip_id = ? AND t1.istek_durumu = 0 AND t2.istek_durumu = 0
    ";
    $stmtTakipKontrol = $conn->prepare($sqlTakipKontrol);
    $stmtTakipKontrol->bind_param("ii", $aktifKullaniciId, $chatKullanici);
    $stmtTakipKontrol->execute();
    $resultTakipKontrol = $stmtTakipKontrol->get_result();
    $rowTakipKontrol = $resultTakipKontrol->fetch_assoc();

    if ($rowTakipKontrol['cnt'] == 1) {

        $mesaj = $conn->real_escape_string(trim($_POST['mesaj']));
        $sqlInsert = "INSERT INTO mesajlasma (icerik, gonderen_id, alici_id) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sii", $mesaj, $aktifKullaniciId, $chatKullanici);
        $stmtInsert->execute();
        header("Location: ?chatWith=$chatKullanici");
        exit;
    } else {

        echo "<script>alert('Mesaj gönderebilmek için karşılıklı olarak birbirinizi takip etmelisiniz.');</script>";
    }
}

$sqlTakipEdilenler = "
    SELECT k.kullanici_id, k.kullanici_adi, k.profil_fotografi
    FROM takiplesme t
    INNER JOIN kullanici k ON t.takip_id = k.kullanici_id
    WHERE t.takipci_id = ? AND t.istek_durumu = 0
    ORDER BY k.kullanici_adi ASC
";
$stmtTakip = $conn->prepare($sqlTakipEdilenler);
$stmtTakip->bind_param("i", $aktifKullaniciId);
$stmtTakip->execute();
$resultTakip = $stmtTakip->get_result();
$kullanicilar = [];
while ($row = $resultTakip->fetch_assoc()) {
    $kullanicilar[] = $row;
}

$mesajlar = [];
if ($chatKullanici) {
    $sqlMesajlar = "
        SELECT m.*, g.kullanici_adi AS gonderen_adi 
        FROM mesajlasma m 
        INNER JOIN kullanici g ON m.gonderen_id = g.kullanici_id
        WHERE (m.gonderen_id = ? AND m.alici_id = ?) 
           OR (m.gonderen_id = ? AND m.alici_id = ?)
        ORDER BY m.gonderilme_tarihi ASC
    ";
    $stmtMesaj = $conn->prepare($sqlMesajlar);
    $stmtMesaj->bind_param("iiii", $aktifKullaniciId, $chatKullanici, $chatKullanici, $aktifKullaniciId);
    $stmtMesaj->execute();
    $resultMesaj = $stmtMesaj->get_result();
    while ($row = $resultMesaj->fetch_assoc()) {
        $mesajlar[] = $row;
    }

    $sqlUpdateOkundu = "
        UPDATE mesajlasma
        SET okundu_durumu = TRUE, okunma_tarihi = NOW()
        WHERE alici_id = ? AND gonderen_id = ? AND okundu_durumu = FALSE
    ";
    $stmtUpdate = $conn->prepare($sqlUpdateOkundu);
    $stmtUpdate->bind_param("ii", $aktifKullaniciId, $chatKullanici);
    $stmtUpdate->execute();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mesajlaşma Uygulaması</title>
    <link rel="stylesheet" href="message.css" />
</head>
<header>
    <a href="../home page/home_page.php">
        <p>Forumsizin.com</p>
    </a>
</header>

<body>
    <div class="duzeltme">
        <div class="sidebar">
            <h3>Takip Ettiklerin</h3>
            <?php if (count($kullanicilar) === 0): ?>
                <p>Henüz kimseyi takip etmiyorsunuz.</p>
            <?php else: ?>
                <?php foreach ($kullanicilar as $kullanici): ?>
                    <div class="profile <?= ($chatKullanici == $kullanici['kullanici_id']) ? 'active' : '' ?>">
                        <img class="pp" src="<?php echo htmlspecialchars('../resimler/' . basename($kullanici['profil_fotografi'])); ?>" alt="Profil Resmi">
                        <a href="?chatWith=<?= $kullanici['kullanici_id'] ?>">
                            <?= htmlspecialchars($kullanici['kullanici_adi']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="main">
            <?php if (!$chatKullanici): ?>
                <p>Lütfen sohbet etmek için bir kullanıcı seçin.</p>
            <?php else: ?>
                <div class="chat-box" id="chatBox">
                    <?php if (count($mesajlar) === 0): ?>
                        <p>Henüz mesaj yok. İlk mesajı siz gönderin!</p>
                    <?php else: ?>
                        <?php foreach ($mesajlar as $mesaj): ?>
                            <div class="message <?= ($mesaj['gonderen_id'] == $aktifKullaniciId) ? 'sent' : 'received' ?>">
                                <strong><?= htmlspecialchars($mesaj['gonderen_adi']) ?>:</strong>
                                <span><?= nl2br(htmlspecialchars($mesaj['icerik'])) ?></span>
                                <div class="time"><?= $mesaj['gonderilme_tarihi'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="input-area">
                    <form method="POST" action="?chatWith=<?= $chatKullanici ?>">
                        <input type="text" name="mesaj" id="messageInput" placeholder="Mesajınızı yazın..." required />
                        <button type="submit">Gönder</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>