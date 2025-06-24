<?php
session_start();
$error_message = "";

if (!isset($_SESSION['email'])) {
    header('Location: ../login/login.php');
    exit();
}

$oturum_email = $_SESSION['email'];

$conn = new mysqli("localhost", "root", "", "forumsizin");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$query = "SELECT * FROM kullanici WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $oturum_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $profile_image_path = $user['profil_fotografi'];

    if (strlen($username) < 3) {
        $error_message = "Kullanıcı adı en az 3 karakter olmalıdır.";
    } else {
        $check_query = "SELECT * FROM kullanici WHERE (email = ? OR kullanici_adi = ?) AND email != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("sss", $email, $username, $oturum_email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
        }
    }

    if (empty($error_message)) {

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    $profile_image_path = $target_file;
                }
            }
        }

        $update_query = "UPDATE kullanici SET email = ?, kullanici_adi = ?, telefon_numarasi = ?, dogum_tarihi = ?, profil_fotografi = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssss", $email, $username, $phone, $dob, $profile_image_path, $oturum_email);


        if ($update_stmt->execute()) {
            $_SESSION['email'] = $email;
            header("Location: profile_settings.php?success=1");
            exit();
        } else {
            $error_message = "Güncelleme başarısız: " . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili Düzenle</title>
    <link rel="stylesheet" href="profile_settings.css">

</head>

<body>
    <form method="POST" action="" enctype="multipart/form-data">
        <header class="custom-header">
            <a href="../home page/home_page.php" class="site-name">Forumsizin.com</a>
            <h1 class="page-title">Profili Düzenle</h1>
        </header>

        <div class="wrapper">
            <div class="image">
                <img id="profile-img" src="<?php echo $user['profil_fotografi']; ?>" alt="Profile Image" style="max-width: 200px; max-height: 200px;">

                <input type="file" name="profile_image" id="profile_image" style="display:none;" onchange="previewImage()">

                <button type="button" class="pp" onclick="document.getElementById('profile_image').click()">Resim Seç</button>

            </div>
            <div class="input">
                <div class="email-password">
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                    <label for="email">E-mail</label>
                </div>
                <div class="email-password">
                    <input type="text" name="username" value="<?php echo $user['kullanici_adi']; ?>" required>
                    <label for="username">Kullanıcı Adı</label>
                </div>
                <div class="email-password">
                    <input type="text" name="phone" maxlength="10" value="<?php echo $user['telefon_numarasi']; ?>" required>
                    <label>Telefon numarası</label>
                </div>
                <div class="email-password">
                    <input type="date" name="dob" value="<?php echo $user['dogum_tarihi']; ?>" required>
                    <label>Doğum tarihi</label>
                </div>
                <button class="submit" type="submit">Kaydet</button>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error_message)): ?>
                    <p style="color:red; text-align:center; font-weight:bold;"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <p style="color: green; text-align: center; font-weight: bold;">Profiliniz başarıyla güncellendi!</p>
                <?php endif; ?>

            </div>
        </div>
    </form>
    <script src="profile_settings.js"></script>

</body>

</html>