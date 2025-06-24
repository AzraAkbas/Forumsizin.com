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

if (!isset($_SESSION['email'])) {
    echo "Kullanıcı girişi gerekli.";
    exit;
}

$email = $_SESSION['email'];
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['term'])) {
    header('Content-Type: application/json; charset=utf-8');
    $term = trim($_GET['term']);
    if ($term === '') {
        echo json_encode([]);
        exit;
    }
    $term_esc = $conn->real_escape_string($term);
    $suggestions = [];

    $sqlKategori = "SELECT kategori_adi AS name FROM kategori WHERE kategori_adi LIKE '$term_esc%' LIMIT 5";
    $resKategori = $conn->query($sqlKategori);
    if ($resKategori) {
        while ($row = $resKategori->fetch_assoc()) {
            $suggestions[] = ['type' => 'Kategori', 'name' => $row['name']];
        }
    }

    $sqlBaslik = "SELECT baslik_adi AS name FROM baslik WHERE baslik_adi LIKE '$term_esc%' LIMIT 5";
    $resBaslik = $conn->query($sqlBaslik);
    if ($resBaslik) {
        while ($row = $resBaslik->fetch_assoc()) {
            $suggestions[] = ['type' => 'Başlık', 'name' => $row['name']];
        }
    }

    $sqlKullanici = "SELECT kullanici_adi AS name FROM kullanici WHERE kullanici_adi LIKE '$term_esc%' LIMIT 5";
    $resKullanici = $conn->query($sqlKullanici);
    if ($resKullanici) {
        while ($row = $resKullanici->fetch_assoc()) {
            $suggestions[] = ['type' => 'Kullanıcı', 'name' => $row['name']];
        }
    }

    echo json_encode($suggestions);
    exit;
}

$sql = "SELECT kullanici_adi, profil_fotografi FROM kullanici WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Kullanıcı bulunamadı.";
    exit;
}
$user = $result->fetch_assoc();
$profil_fotografi = $user['profil_fotografi'] ?: "default.jpg";
$kullanici_adi = $user['kullanici_adi'];

if (!isset($_GET['kategori_adi'])) {
    echo "Kategori seçilmedi.";
    exit;
}

$kategori_adi = $_GET['kategori_adi'];

$stmt = $conn->prepare("SELECT kategori_id FROM kategori WHERE kategori_adi = ?");
$stmt->bind_param("s", $kategori_adi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Kategori bulunamadı.";
    exit;
}

$row = $result->fetch_assoc();
$kategori_id = $row['kategori_id'];


?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($kategori_adi); ?> - Başlıklar</title>
    <link rel="stylesheet" href="title.css">
</head>

<body>
    <header>
        <a href="../home page/home_page.php">
            <p>Forumsizin.com</p>
        </a>
    </header>

    <nav>
        <form class="search-box" action="" method="GET" autocomplete="off">
            <input type="text" id="search_input" name="search_query" placeholder="Kategori/Başlık/Kullanıcı Ara"
                value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" />
            <button type="submit">Ara</button>
            <ul id="suggestions" class="suggestions" style="display:none;"></ul>
        </form>
        <a href="../profile/profile.php">
            <img src="<?php echo htmlspecialchars('../resimler/' . basename($profil_fotografi)); ?>" alt="Profil Resmi">
        </a>
    </nav>

    <div class="container">
        <aside class="aside-left">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam">
        </aside>

        <main>
            <h3><?php echo htmlspecialchars($kategori_adi); ?> - Başlıklar</h3>
            <ul>
                <?php
                $stmt = $conn->prepare("SELECT baslik_adi, baslik_id FROM baslik WHERE kategori_id = ?");
                $stmt->bind_param("i", $kategori_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $baslik_adi = $row['baslik_adi'];
                        echo "<li><a class='baslik-link' href='../title post/title_post.php?baslik_id={$row['baslik_id']}&baslik=" . urlencode($baslik_adi) . "&kategori_id={$kategori_id}&kategori_adi=" . urlencode($kategori_adi) . "'>" . htmlspecialchars($baslik_adi) . "</a></li>";
                    }
                } else {
                    echo "<li>Bu kategoriye ait başlık bulunamadı.</li>";
                }
                ?>
            </ul>
        </main>

        <aside class="aside-left">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam">
        </aside>
    </div>

    <footer>
        <p>Bizimle iletişime geçmek için <a href="mailto:azraaakbas@gmail.com">azraaakbas@gmail.com</a></p>
    </footer>

    <script src="title.js"></script>
</body>

</html>
<?php
$conn->close();
?>