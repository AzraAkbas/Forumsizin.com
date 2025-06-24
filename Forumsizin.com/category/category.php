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


if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    $sql = "SELECT profil_fotografi FROM kullanici WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $profil_fotografi = $row['profil_fotografi'];
    } else {
        $profil_fotografi = "default.jpg";
    }
} else {
    echo "Kullanıcı girişi gerekli.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forumsizin - Kategoriler</title>
    <link rel="stylesheet" href="category.css" />
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
            <img src="<?php echo htmlspecialchars('../resimler/' . basename($profil_fotografi)); ?>" alt="Profil Resmi" />
        </a>
    </nav>

    <div class="container">
        <aside class="aside-left">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
        </aside>

        <main>
            <h3>Kategoriler</h3>
            <ul>
                <?php
                $sql = "SELECT kategori_adi FROM kategori";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><a href='../title/title.php?kategori_adi=" . urlencode($row["kategori_adi"]) . "'>" . htmlspecialchars($row["kategori_adi"]) . "</a></li>";
                    }
                } else {
                    echo "<li>Hiç kategori bulunamadı.</li>";
                }
                ?>
            </ul>
        </main>

        <aside class="aside-left">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKirUvOUa021MiXlrF0dgtKEb8QfZHRGNSrw&s" alt="reklam" />
        </aside>
    </div>

    <footer>
        <p>Bizimle iletişime geçmek için <a href="mailto:azraaakbas@gmail.com">azraaakbas@gmail.com</a></p>
    </footer>

    <script src="category.js"></script>

    <?php
    $conn->close();
    ?>
</body>

</html>