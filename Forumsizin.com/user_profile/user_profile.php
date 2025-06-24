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

$giris_yapan_email = $_SESSION['email'] ?? null;
$giris_yapan_id = null;

if ($giris_yapan_email) {
  $stmt = $conn->prepare("SELECT kullanici_id FROM kullanici WHERE email = ?");
  $stmt->bind_param("s", $giris_yapan_email);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $giris_yapan_id = $row['kullanici_id'];
  }
}


if (!isset($_GET['kullanici_adi']) || empty(trim($_GET['kullanici_adi']))) {
  echo "Kullanıcı belirtilmedi.";
  exit;
}

$kullanici_adi = $_GET['kullanici_adi'];

$stmt = $conn->prepare("SELECT * FROM kullanici WHERE kullanici_adi = ?");
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "Kullanıcı bulunamadı.";
  exit;
}

$kullanici = $result->fetch_assoc();

$stmt = $conn->prepare("
SELECT 
    f.*, 
    b.baslik_adi, 
    k.kategori_adi, 
    u.kullanici_adi, 
    u.profil_fotografi,
    (
        SELECT e.simge
        FROM emoji_birakilma eb
        JOIN emoji e ON eb.emoji_id = e.emoji_id
        WHERE eb.fotograf_id = f.fotograf_id
          AND eb.kullanici_id = ?
        LIMIT 1
    ) AS secilen_emoji
FROM fotograf f
JOIN baslik b ON f.baslik_id = b.baslik_id
LEFT JOIN kategori k ON b.kategori_id = k.kategori_id
JOIN kullanici u ON f.kullanici_id = u.kullanici_id
WHERE f.kullanici_id = ?
ORDER BY f.paylasilma_tarihi DESC

");


$stmt->bind_param("ii", $giris_yapan_id, $kullanici['kullanici_id']);

$stmt->execute();
$paylasilanlar = $stmt->get_result();


$stmt = $conn->prepare("SELECT COUNT(*) FROM takiplesme WHERE takip_id = ?");
$stmt->bind_param("i", $kullanici['kullanici_id']);
$stmt->execute();
$stmt->bind_result($takipci_sayisi);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM takiplesme WHERE takipci_id = ?");
$stmt->bind_param("i", $kullanici['kullanici_id']);
$stmt->execute();
$stmt->bind_result($takip_edilen_sayisi);
$stmt->fetch();
$stmt->close();

$takip_ediliyor = false;
if ($giris_yapan_id && $giris_yapan_id != $kullanici['kullanici_id']) {
  $stmt = $conn->prepare("SELECT 1 FROM takiplesme WHERE takipci_id = ? AND takip_id = ? LIMIT 1");
  $stmt->bind_param("ii", $giris_yapan_id, $kullanici['kullanici_id']);
  $stmt->execute();
  $stmt->store_result();
  $takip_ediliyor = $stmt->num_rows > 0;
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($kullanici['kullanici_adi']); ?> - Profil</title>
  <link rel="stylesheet" href="user_profile.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap">
</head>

<body>

  <header>
    <a href="../home page/home_page.php">
      <p>Forumsizin.com</p>
    </a>
  </header>

  <div class="info">
    <div class="profiles">
      <div class="profile-info">
        <img src="<?php echo htmlspecialchars('../resimler/' . basename($kullanici['profil_fotografi'])); ?>" alt="Profil Resmi">
        <?php if ($giris_yapan_id !== null): ?>
          <button id="follow" data-user-id="<?php echo $kullanici['kullanici_id']; ?>" <?php echo ($giris_yapan_id == $kullanici['kullanici_id']) ? 'disabled' : ''; ?>>
            <?php
            if ($giris_yapan_id == $kullanici['kullanici_id']) {
              echo "Bu sensin";
            } else {
              echo $takip_ediliyor ? "Takiptesin" : "Takip Et";
            }
            ?>
          </button>
        <?php else: ?>
          <button disabled>Giriş yapmalısınız</button>
        <?php endif; ?>
      </div>
      <p class="username"><?php echo htmlspecialchars($kullanici['kullanici_adi']); ?></p>
    </div>

    <div class="follower-info">
      <div class="follower">
        <p>Takipçi</p>
        <p id="follower-count"><?php echo $takipci_sayisi; ?></p>
      </div>
      <div class="follower">
        <p>Takip Edilenler</p>
        <p><?php echo $takip_edilen_sayisi; ?></p>
      </div>
    </div>
  </div>

  <main>
    <h3>Paylaşılanlar</h3>
    <?php while ($post = $paylasilanlar->fetch_assoc()): ?>
      <div class="card post">
        <div class="profile">
          <img class="pp" src="<?php echo htmlspecialchars('../resimler/' . basename($post['profil_fotografi'])); ?>" alt="Profil Resmi">
          <a href="../user_profile/user_profile.php?kullanici_adi=<?php echo urlencode($post['kullanici_adi']); ?>">
            <?php echo htmlspecialchars($post['kullanici_adi']); ?>
          </a>
        </div>
        <img class="post-resim" src="<?php echo htmlspecialchars('../resimler/' . $post['paylasilan_fotograf']); ?>" alt="...">
        <div class="card-body">
          <div class="post-category-title">
            <?= htmlspecialchars(($post['kategori_adi'] ?? 'Kategori Yok') . ' - ' . ($post['baslik_adi'] ?? 'Başlık Yok')) ?>
          </div>
          <p class="card-text"><?php echo htmlspecialchars($post['aciklama']); ?></p>
          <div class="icons">
            <span id="emojiIcon-<?php echo $post['fotograf_id']; ?>" class="material-symbols-outlined mood-icon" onclick="toggleEmojiBox(<?php echo $post['fotograf_id']; ?>, this)">
              <?php echo $post['secilen_emoji'] ? htmlspecialchars($post['secilen_emoji']) : 'mood'; ?>
            </span>
            <div id="emojiBox-<?php echo $post['fotograf_id']; ?>" class="emoji-box" style="display:none;"></div>
            <span class="material-symbols-outlined comment-icon" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#commentModal" data-fotograf-id="<?php echo $post['fotograf_id']; ?>">
              forum
            </span>
            <span class="material-symbols-outlined bookmark-icon">bookmark</span>
            <span class="material-symbols-outlined report-icon"
              data-bs-toggle="modal" data-bs-target="#reportModal"
              data-fotograf-id="<?= htmlspecialchars($post['fotograf_id']) ?>"
              style="cursor:pointer;">
              report_problem
            </span>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </main>

  <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="commentModalLabel">Yorumlar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <div id="commentList"></div>
          <textarea class="form-control mt-3" id="commentInput" rows="2" placeholder="Yorumunuzu yazın..."></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" id="commentSubmit">Gönder</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reportModalLabel">Bu gönderiyi neden bildiriyorsunuz?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <form id="reportForm">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Spam" id="spam">
              <label class="form-check-label" for="spam">Spam</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Nefret Söylemi" id="hate">
              <label class="form-check-label" for="hate">Nefret Söylemi</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Müstehcen İçerik" id="obscene">
              <label class="form-check-label" for="obscene">Müstehcen İçerik</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Diğer" id="other">
              <label class="form-check-label" for="other">Diğer</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Alakasız" id="weird">
              <label class="form-check-label" for="weird">Alakasız İçerik</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="button" class="btn btn-danger" id="submitReport">Bildir</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="yorumReportModal" tabindex="-1" aria-labelledby="yorumReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="yorumReportModalLabel">Yorumu neden bildiriyorsunuz?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <form id="yorumReportForm">
            <input type="hidden" name="yorum_id" id="reportYorumId" value="">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Spam" id="yorumSpam">
              <label class="form-check-label" for="yorumSpam">Spam</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Nefret Söylemi" id="yorumHate">
              <label class="form-check-label" for="yorumHate">Nefret Söylemi</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Müstehcen İçerik" id="yorumObscene">
              <label class="form-check-label" for="yorumObscene">Müstehcen İçerik</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reportReason" value="Diğer" id="yorumOther">
              <label class="form-check-label" for="yorumOther">Diğer</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="button" class="btn btn-danger" id="submitYorumReport">Bildir</button>
        </div>
      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="user_profile.js" defer></script>
  <script src="../islemler javascript/emoji.js"></script>
  <script src="../islemler javascript/fotograf_bildir.js"></script>
  <script src="../islemler javascript/kaydet.js"></script>
  <script src="../islemler javascript/yorum_bildir.js"></script>
  <script src="../islemler javascript/yorum.js"></script>

</body>

</html>

<?php
$conn->close();
?>