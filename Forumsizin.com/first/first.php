<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$sql = "
SELECT 
    f.fotograf_id,
    k.kullanici_adi,
    k.profil_fotografi,
    ka.kategori_adi,
    b.baslik_adi,
    f.aciklama,
    f.paylasilan_fotograf,
    COUNT(eb.emoji_id) AS emoji_count
FROM fotograf f
JOIN kullanici k ON f.kullanici_id = k.kullanici_id
JOIN kategori ka ON f.kategori_id = ka.kategori_id
JOIN baslik b ON f.baslik_id = b.baslik_id
LEFT JOIN emoji_birakilma eb ON eb.fotograf_id = f.fotograf_id
GROUP BY f.fotograf_id
ORDER BY emoji_count DESC
LIMIT 12
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>En Popüler Fotoğraflar - Forumsizin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="first.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" />
</head>
<body>
  <header>
    <a href=""><p>Forumsizin.com</p></a>
  </header>

  <nav>
    <button id="register" class="register">Kayıt Ol</button>
    <button id="login" class="login">Giriş Yap</button>
  </nav>

  <main>
    <h3 >Daha Fazlası İçin Giriş Yap/Kayıt Ol</h3>

    <?php
    if ($result && $result->num_rows > 0) {
        while ($post = $result->fetch_assoc()) {
            ?>
            <div class="card post">
                <div class="profile">
                    <img class="pp" src="<?php echo htmlspecialchars('../resimler/' . basename($post['profil_fotografi'])); ?>" alt="Profil Resmi" />
                    <a href="../user_profile/user_profile.php?kullanici_adi=<?php ($post['kullanici_adi']); ?>">
                        <?php echo htmlspecialchars($post['kullanici_adi']); ?>
                    </a>
                </div>
                <img class="post-resim" src="<?php echo htmlspecialchars('../resimler/' . $post['paylasilan_fotograf']); ?>" alt="..." />
                <div class="card-body">
                    <div class="post-category-title">
                        <?= htmlspecialchars(($post['kategori_adi'] ?? 'Kategori Yok') . ' - ' . ($post['baslik_adi'] ?? 'Başlık Yok')) ?>
                    </div>
                    <p class="card-text"><?php echo htmlspecialchars($post['aciklama']); ?></p>
                    <div class="icons">
                        <span class="material-symbols-outlined mood-icon">mood</span>
                        <span class="material-symbols-outlined comment-icon">forum</span>
                        <span class="material-symbols-outlined bookmark-icon">bookmark</span>
                        <span class="material-symbols-outlined report-icon">report_problem</span>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p>Hiç fotoğraf bulunamadı.</p>';
    }
    ?>
  </main>

  <footer class="text-center p-3">
    <p>Bizimle iletişime geçmek için <a href="mailto:azraaakbas@gmail.com">azraaakbas@gmail.com</a></p>
  </footer>


  <div class="modal fade" id="loginAlertModal" tabindex="-1" aria-labelledby="loginAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: #2a2058; color: white;">
        <div class="modal-header">
          <h5 class="modal-title" id="loginAlertModalLabel">Dikkat!</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          Lütfen önce giriş yapınız veya kayıt olunuz.
        </div>
        <div class="modal-footer">
          <a href="../login/login.php" class="btn btn-success">Giriş Yap</a>
          <a href="../register/register.php" class="btn btn-primary">Kayıt Ol</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="first.js"></script>
</body>
</html>
