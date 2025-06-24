<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "forumsizin");
if (!$conn) {
    die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
}

if (!isset($_SESSION['email'])) {
    header('Location: ../login/login.php');
    exit();
}

$admin_id = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['kategori_sil'])) {
        $id = intval($_POST['kategori_id']);
        $stmt = $conn->prepare("DELETE FROM kategori WHERE kategori_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['baslik_sil'])) {
        $id = intval($_POST['baslik_id']);
        $stmt = $conn->prepare("DELETE FROM baslik WHERE baslik_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['kategori_ekle'])) {
        $ad = $_POST['kategori_adi'];
        $stmt = $conn->prepare("INSERT INTO kategori (kategori_adi, admin_id) VALUES (?, ?)");
        $stmt->bind_param("si", $ad, $admin_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['baslik_ekle'])) {
        $ad = $_POST['baslik_adi'];
        $kat_id = intval($_POST['kategori_id_yeni']);
        $stmt = $conn->prepare("INSERT INTO baslik (baslik_adi, kategori_id, admin_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $ad, $kat_id, $admin_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['foto_sil'])) {
        $foto_id = intval($_POST['foto_id']);
        $bildirme_id = intval($_POST['bildirme_id']);

        $stmt = $conn->prepare("DELETE FROM fotograf WHERE fotograf_id = ?");
        $stmt->bind_param("i", $foto_id);
        $stmt->execute();
        $stmt->close();

        $karar = 1;
        $check = $conn->prepare("SELECT * FROM karar_verme WHERE bildirme_id = ?");
        $check->bind_param("i", $bildirme_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO karar_verme (karar, admin_id, bildirme_id) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $karar, $admin_id, $bildirme_id);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }

    if (isset($_POST['yorum_sil'])) {
        $yorum_id = intval($_POST['yorum_id']);
        $bildirme_id = intval($_POST['bildirme_id']);

        $stmt = $conn->prepare("DELETE FROM yorum WHERE yorum_id = ?");
        $stmt->bind_param("i", $yorum_id);
        $stmt->execute();
        $stmt->close();

        $karar = 1;
        $check = $conn->prepare("SELECT * FROM karar_verme WHERE bildirme_id = ?");
        $check->bind_param("i", $bildirme_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO karar_verme (karar, admin_id, bildirme_id) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $karar, $admin_id, $bildirme_id);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }

    if (isset($_POST['karar_reddet'])) {
        $bildirme_id = intval($_POST['bildirme_id']);
        $karar = 0;

        $check = $conn->prepare("SELECT * FROM karar_verme WHERE bildirme_id = ?");
        $check->bind_param("i", $bildirme_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO karar_verme (karar, admin_id, bildirme_id) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $karar, $admin_id, $bildirme_id);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }

    header("Location: admin.php");
    exit();
}

$kategoriler = $conn->query("SELECT * FROM kategori");
$basliklar = $conn->query("SELECT * FROM baslik");

$bildirilen_fotograflar = $conn->query("
SELECT
    f.fotograf_id,
    f.paylasilan_fotograf,
    f.aciklama,
    f.paylasilma_tarihi,
    k.kullanici_adi,
    k.profil_fotografi,
    b.bildirme_id,
    b.bildirme_tarihi,
    bs.baslik_adi,
    kt.kategori_adi
FROM fotograf_bildirme fb
INNER JOIN bildirme b ON fb.bildirme_id = b.bildirme_id
INNER JOIN fotograf f ON fb.fotograf_id = f.fotograf_id
INNER JOIN kullanici k ON f.kullanici_id = k.kullanici_id
LEFT JOIN baslik bs ON f.baslik_id = bs.baslik_id
LEFT JOIN kategori kt ON bs.kategori_id = kt.kategori_id
LEFT JOIN karar_verme kv ON kv.bildirme_id = b.bildirme_id
WHERE kv.bildirme_id IS NULL
ORDER BY b.bildirme_tarihi DESC
");

$bildirilen_yorumlar = $conn->query("
SELECT
    y.yorum_id,
    y.yorum_icerik,
    k.kullanici_adi,
    k.profil_fotografi,
    b.bildirme_id,
    b.bildirme_tarihi
FROM yorum_bildirme yb
INNER JOIN bildirme b ON yb.bildirme_id = b.bildirme_id
INNER JOIN yorum y ON yb.yorum_id = y.yorum_id
INNER JOIN kullanici_yorum ky ON ky.yorum_id = y.yorum_id
INNER JOIN kullanici k ON ky.kullanici_id = k.kullanici_id
LEFT JOIN karar_verme kv ON kv.bildirme_id = b.bildirme_id
WHERE kv.bildirme_id IS NULL
ORDER BY b.bildirme_tarihi DESC
");
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="admin.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container my-4">
        <header>
        <a href="../home page/home_page.php"><p>Forumsizin.com</p></a>
    </header>
    <h1>Admin Paneli</h1>

    <!-- Kategori Sil -->
    <form method="POST" class="mb-4">
        <h2>Kategori Sil</h2>
        <select name="kategori_id" class="form-select" required>
            <?php mysqli_data_seek($kategoriler, 0); ?>
            <?php while ($row = mysqli_fetch_assoc($kategoriler)): ?>
                <option value="<?= $row['kategori_id'] ?>"><?= htmlspecialchars($row['kategori_adi']) ?></option>
            <?php endwhile; ?>
        </select>
        <button name="kategori_sil" class="btn btn-danger mt-2">Sil</button>
    </form>

    <!-- Başlık Sil -->
    <form method="POST" class="mb-4">
        <h2>Başlık Sil</h2>
        <select name="baslik_id" class="form-select" required>
            <?php mysqli_data_seek($basliklar, 0); ?>
            <?php while ($row = mysqli_fetch_assoc($basliklar)): ?>
                <option value="<?= $row['baslik_id'] ?>"><?= htmlspecialchars($row['baslik_adi']) ?></option>
            <?php endwhile; ?>
        </select>
        <button name="baslik_sil" class="btn btn-danger mt-2">Sil</button>
    </form>

    <!-- Yeni Kategori Ekle -->
    <form method="POST" class="mb-4">
        <h2>Yeni Kategori Ekle</h2>
        <input type="text" name="kategori_adi" class="form-control" required />
        <button name="kategori_ekle" class="btn btn-primary mt-2">Ekle</button>
    </form>

    <!-- Yeni Başlık Ekle -->
    <form method="POST" class="mb-4">
        <h2>Yeni Başlık Ekle</h2>
        <input type="text" name="baslik_adi" class="form-control mb-2" required />
        <select name="kategori_id_yeni" class="form-select mb-2" required>
            <?php
            $kategoriler2 = mysqli_query($conn, "SELECT * FROM kategori");
            while ($row = mysqli_fetch_assoc($kategoriler2)): ?>
                <option value="<?= $row['kategori_id'] ?>"><?= htmlspecialchars($row['kategori_adi']) ?></option>
            <?php endwhile; ?>
        </select>
        <button name="baslik_ekle" class="btn btn-primary">Ekle</button>
    </form>

<h2>Bildirilen Fotoğraflar</h2>
<div class="scroll-container">
    <?php while ($foto = mysqli_fetch_assoc($bildirilen_fotograflar)): ?>
        <div class="card post">
            <div class="profile">
                <img class="pp " src="<?= htmlspecialchars('../resimler/' . basename($foto['profil_fotografi'])) ?>" alt="Profil Resmi" />
                    <?= htmlspecialchars($foto['kullanici_adi']) ?>
                </a>
            </div>
            <img class="post-resim " src="<?= htmlspecialchars('../resimler/' . $foto['paylasilan_fotograf']) ?>" alt="Fotoğraf" />
            <div class="card-body">
                <div class="post-category-title mb-2">
                    <?= htmlspecialchars(($foto['kategori_adi'] ?? 'Kategori Yok') . ' - ' . ($foto['baslik_adi'] ?? 'Başlık Yok')) ?>
                </div>
                <p class="card-text"><?= htmlspecialchars($foto['aciklama']) ?></p>

                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="foto_id" value="<?= $foto['fotograf_id'] ?>" />
                    <input type="hidden" name="bildirme_id" value="<?= $foto['bildirme_id'] ?>" />
                    <button type="submit" name="foto_sil" class="btn btn-danger ">Sil</button>
                    <button type="submit" name="karar_reddet" class="btn btn-secondary ">Kalsın (İhlal Yok)</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>


  
<h2 class="mt-5">Bildirilen Yorumlar</h2>
<div class="scroll-container">
    <?php while ($yorum = mysqli_fetch_assoc($bildirilen_yorumlar)): ?>
        <div class="comment-card">
            <div class="profile">
                <img class="pp" src="<?= htmlspecialchars('../resimler/' . basename($yorum['profil_fotografi'])) ?>" alt="Profil Resmi" />
                <a href="../user_profile/user_profile.php?kullanici_adi=<?= urlencode($yorum['kullanici_adi']) ?>">
                    <?= htmlspecialchars($yorum['kullanici_adi']) ?>
                </a>
            </div>
            <div class="comment-body">
                <p class="comment-text"><?= htmlspecialchars($yorum['yorum_icerik']) ?></p>
                <form method="POST" class="mb-1">
                    <input type="hidden" name="yorum_id" value="<?= $yorum['yorum_id'] ?>" />
                    <button name="yorum_sil" class="btn btn-danger ">Sil</button>
                </form>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="bildirme_id" value="<?= $yorum['bildirme_id'] ?>" />
                    <button name="karar_reddet" class="btn btn-secondary ">İhlal Yok</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
