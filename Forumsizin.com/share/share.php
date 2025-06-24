<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

if (!isset($_SESSION['email'])) {
    echo "Giriş yapmalısınız.";
    exit;
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT kullanici_id, kullanici_adi, profil_fotografi FROM kullanici WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();
if ($userResult->num_rows == 0) {
    echo "Kullanıcı bulunamadı.";
    exit;
}
$user = $userResult->fetch_assoc();
$kullanici_id = $user['kullanici_id'];
$kullanici_adi = $user['kullanici_adi'];
$profil_resmi = $user['profil_fotografi'];

$baslik_adi = $_GET['baslik'] ?? 'Bilinmeyen Başlık';

$stmt = $conn->prepare("SELECT baslik_id, kategori_id FROM baslik WHERE baslik_adi = ?");
$stmt->bind_param("s", $baslik_adi);
$stmt->execute();
$baslikResult = $stmt->get_result();

if ($baslikResult->num_rows == 0) {
    $kategori_adi = "Bilinmeyen Kategori";
    $baslik_id = 0;
} else {
    $row = $baslikResult->fetch_assoc();
    $kategori_id = $row['kategori_id'];
    $baslik_id = $row['baslik_id'];

    $stmt = $conn->prepare("SELECT kategori_adi FROM kategori WHERE kategori_id = ?");
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
    $kategoriResult = $stmt->get_result();
    if ($kategoriResult->num_rows > 0) {
        $kategoriRow = $kategoriResult->fetch_assoc();
        $kategori_adi = $kategoriRow['kategori_adi'];
    } else {
        $kategori_adi = "Bilinmeyen Kategori";
    }
}

$mesaj = "";
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aciklama = $_POST['aciklama'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = "../resimler/";
        $file_name = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO fotograf (kullanici_id, kategori_id, baslik_id, paylasilan_fotograf, aciklama) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $kullanici_id, $kategori_id, $baslik_id, $file_name, $aciklama);
            $stmt->execute();


            $_SESSION['mesaj'] = "Paylaşım başarılı!";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $mesaj = "Resim yüklenemedi.";
        }
    } else {
        $mesaj = "Lütfen bir resim seçin.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Resim Yükle ve Açıklama Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="share.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap">
</head>

<body>
    <header>
        <a href="../home page/home_page.php">
            <p>Forumsizin.com</p>
        </a>
    </header>
    <div class="upload">
        <form method="POST" enctype="multipart/form-data">
            <label for="imageUpload" class="upload-btn">Resim Seç</label>
            <input type="file" id="imageUpload" name="image" accept="image/*" onchange="previewImage()" />

            <div class="container">
                <div class="card post">
                    <div class="profile">
                        <img class="pp" src="<?php echo htmlspecialchars('../resimler/' . basename($profil_resmi)); ?>" alt="Profil Resmi">
                        <div class="profile_name">
                            <?php echo htmlspecialchars($kullanici_adi); ?>
                        </div>
                    </div>

                    <img id="preview" class="post-resim" src="" alt="Yüklenen Resim" />

                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($kategori_adi); ?> - <?php echo htmlspecialchars($baslik_adi); ?></h6>
                        <textarea id="description" name="aciklama" placeholder="Açıklamanızı buraya yazın..." rows="3"></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="share btn btn-primary mt-3">Paylaş</button>
        </form>
    </div>

    <?php if (!empty($mesaj)): ?>
        <div style="
            margin-right:140px;
            color:rgb(44, 166, 64);
            font-weight: bold;
        ">
            <?php echo htmlspecialchars($mesaj); ?>
        </div>
    <?php endif; ?>

    <script src="share.js"></script>
</body>

</html>