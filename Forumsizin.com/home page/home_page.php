<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: ../login/login.php');
    exit();
}

$user_email = $_SESSION['email'];


$is_admin = false;
$sql_admin = "SELECT * FROM admin WHERE admin_mail = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("s", $user_email);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
if ($result_admin->num_rows > 0) {
    $is_admin = true;
}


$sql_user = "SELECT profil_fotografi, kullanici_id FROM kullanici WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

if ($user) {
    $profile_photo = $user['profil_fotografi'];
    $user_id = $user['kullanici_id'];
} else {
    echo "Hata: Kullanıcı bulunamadı.";
    exit();
}


$sql_posts = "

    SELECT 
        f.fotograf_id, 
        f.paylasilan_fotograf, 
        f.aciklama, 
        k.kullanici_adi, 
        k.profil_fotografi,
        e.simge AS secilen_emoji,
        kat.kategori_adi,
        bas.baslik_adi
    FROM fotograf f
    JOIN kullanici k ON f.kullanici_id = k.kullanici_id
    LEFT JOIN emoji_birakilma ke ON ke.fotograf_id = f.fotograf_id AND ke.kullanici_id = ?
    LEFT JOIN emoji e ON ke.emoji_id = e.emoji_id
    LEFT JOIN kategori kat ON f.kategori_id = kat.kategori_id
    LEFT JOIN baslik bas ON f.baslik_id = bas.baslik_id
    WHERE f.kullanici_id IN (
        SELECT takip_id FROM takiplesme WHERE takipci_id = ?
    )
    ORDER BY f.fotograf_id DESC";


$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("ii", $user_id, $user_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil ve Takip Edilenler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="home_page.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" />
</head>

<body>
    <header>
        <a href="home_page.php">
            <p>Forumsizin.com</p>
        </a>
    </header>
    <nav>
        <a href="../category/category.php" id="category" class="category">Kategoriler</a>
        <a href="../save/save.php?user_id=<?= htmlspecialchars($user_id) ?>" id="save" class="save">Kaydedilenler</a>
        <a href="../message/message.php?user_id=<?= htmlspecialchars($user_id) ?>" id="message" class="message">Mesajlar</a>

        <?php if ($is_admin): ?>
            <a href="../admin/admin.php" id="admin" class="message">Admin</a>
        <?php endif; ?>

        <a href="../Profile/profile.php?user_id=<?= htmlspecialchars($user_id) ?>">
            <img src="<?= htmlspecialchars('../resimler/' . basename($profile_photo)) ?>" alt="Profil Resmi" />
        </a>
    </nav>

    <main>
        <h3>Takip Edilenler</h3>

        <?php
        while ($post = $result_posts->fetch_assoc()) {

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
                    <div class="post-category-title">
                        <?= htmlspecialchars(($post['kategori_adi'] ?? 'Kategori Yok') . ' - ' . ($post['baslik_adi'] ?? 'Başlık Yok')) ?>
                    </div>

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
                            data-bs-toggle="modal"
                            data-bs-target="#commentModal"
                            data-fotograf-id="<?php echo $post['fotograf_id']; ?>">
                            forum
                        </span>

                        <span class="material-symbols-outlined bookmark-icon">bookmark</span>

                        <span class="material-symbols-outlined report-icon"
                            data-bs-toggle="modal" data-bs-target="#reportModal"
                            data-fotograf-id="<?= htmlspecialchars($post['fotograf_id']) ?>">
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
                            <input class="form-check-input" type="radio" name="reportReason" value="Alakasız" id="other">
                            <label class="form-check-label" for="weird">Alakasız İçerik</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reportReason" value="Diğer" id="other">
                            <label class="form-check-label" for="other">Diğer</label>
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
    <script src="home_page.js"></script>
    <script src="../islemler javascript/emoji.js"></script>
    <script src="../islemler javascript/fotograf_bildir.js"></script>
    <script src="../islemler javascript/kaydet.js"></script>
    <script src="../islemler javascript/yorum_bildir.js"></script>
    <script src="../islemler javascript/yorum.js"></script>
</body>

</html>

<?php
$stmt_user->close();
$stmt_posts->close();
$conn->close();
?>