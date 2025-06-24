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
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];

  $stmt = $conn->prepare("SELECT kullanici_id, profil_fotografi FROM kullanici WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profil_fotografi = $row['profil_fotografi'];
    $kullanici_id = $row['kullanici_id']; 
  } else {
    $profil_fotografi = "default.jpg";
  }
} else {
  echo "Kullanıcı girişi gerekli.";
  exit;
}

if (!isset($_GET['baslik'])) {
  echo "Başlık seçilmedi.";
  exit;
}

$baslik_adi = $_GET['baslik'];

$stmt = $conn->prepare("SELECT baslik_id FROM baslik WHERE baslik_adi = ?");
$stmt->bind_param("s", $baslik_adi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  echo "Başlık bulunamadı.";
  exit;
}

$row = $result->fetch_assoc();
$baslik_id = $row['baslik_id'];

$stmt = $conn->prepare("
    SELECT 
        f.fotograf_id, 
        f.paylasilan_fotograf, 
        f.aciklama, 
        k.kullanici_adi, 
        k.profil_fotografi,
        e.simge AS secilen_emoji
    FROM fotograf f
    JOIN kullanici k ON f.kullanici_id = k.kullanici_id
    LEFT JOIN emoji_birakilma ke ON ke.fotograf_id = f.fotograf_id AND ke.kullanici_id = ?
    LEFT JOIN emoji e ON ke.emoji_id = e.emoji_id
    WHERE f.baslik_id = ?
    ORDER BY f.fotograf_id DESC
");
$stmt->bind_param("ii", $kullanici_id, $baslik_id);
$stmt->execute();
$postlar = $stmt->get_result();



?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($baslik_adi); ?> - Postlar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="title-post.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap">
</head>

<body>
  <header>
    <a href="../home page/home_page.php">
      <p>Forumsizin.com</p>
    </a>
  </header>
  <nav>
    <a href="../share/share.php?baslik=<?php echo urlencode($baslik_adi); ?>" id="share" class="share">Paylaş</a>
    <a href="../profile/profile.php">
      <img src="<?php echo htmlspecialchars('../resimler/' . basename($profil_fotografi)); ?>" alt="Profil Resmi">
    </a>
  </nav>

  <main>
    <h3><?php echo htmlspecialchars($baslik_adi); ?></h3>

    <?php
    while ($post = $postlar->fetch_assoc()) {

    ?>

      <div class="card post">
        <div class="profile">
          <img class="pp" src="<?php echo htmlspecialchars('../resimler/' . basename($post['profil_fotografi'])); ?>" alt="Profil Resmi">
          <a href="../user_profile/user_profile.php?kullanici_adi=<?php echo urlencode($post['kullanici_adi']); ?>">
            <?php echo htmlspecialchars($post['kullanici_adi']); ?>
          </a>

        </div>
        <img class="post-resim" src="<?php echo htmlspecialchars('../resimler/' . $post['paylasilan_fotograf']); ?>" alt="...">
        <div class="card-body">
          <h6 class="card-title"></h6>
          <p class="card-text"><?php echo htmlspecialchars($post['aciklama']); ?></p>
          <div class="icons">
            <span id="emojiIcon-<?php echo $post['fotograf_id']; ?>"
              class="material-symbols-outlined mood-icon"
              onclick="toggleEmojiBox(<?php echo $post['fotograf_id']; ?>, this)">
              <?php echo $post['secilen_emoji'] ? htmlspecialchars($post['secilen_emoji']) : 'mood'; ?>
            </span>


            <div id="emojiBox-<?php echo $post['fotograf_id']; ?>" class="emoji-box" style="display:none;"></div>

            <span
              class="material-symbols-outlined comment-icon"
              style="cursor: pointer;"
              data-bs-toggle="modal"
              data-bs-target="#commentModal"
              data-fotograf-id="<?php echo $post['fotograf_id']; ?>">
              forum
            </span>


            <span class="material-symbols-outlined bookmark-icon">bookmark
              <i class="fa-regular fa-bookmark"></i>
            </span>
            <span class="material-symbols-outlined report-icon"
              data-bs-toggle="modal" data-bs-target="#reportModal"
              data-fotograf-id="<?= htmlspecialchars($post['fotograf_id']) ?>"
              style="cursor:pointer;">
              report_problem
            </span>
          </div>



        </div>
      </div>

    <?php } ?>

  </main>
  </form>

  </div>

  </div>
  </div>
  </div>


  <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="commentModalLabel">Yorumlar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <div id="commentList">
          </div>
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
              <input class="form-check-input" type="radio" name="reportReason" value="Alakasız" id="other">
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
  <script src="title_post.js"></script>
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