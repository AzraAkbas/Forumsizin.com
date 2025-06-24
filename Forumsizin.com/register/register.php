<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hata = ""; 
$successMessage = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];
    $username = $_POST['username'];

    $check_username_sql = "SELECT * FROM kullanici WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($check_username_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $hata .= "<p>Bu kullanıcı adı zaten kayıtlı!</p>";
    }

    if (empty($hata)) {
        $check_email_sql = "SELECT * FROM kullanici WHERE email = ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $hata .= "<p>Bu e-posta zaten kayıtlı!</p>";
        }
    }

    if (empty($hata)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_sql = "INSERT INTO kullanici (kullanici_adi, email, sifre, telefon_numarasi, dogum_tarihi) 
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $birthdate);

        if ($insert_stmt->execute()) {

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            header("Location: ../home page/home_page.php");
            exit();
        } else {
            $hata .= "<p>Bir hata oluştu: " . $conn->error . "</p>";
        }
    }
}


$conn->close();
?>



<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="stylesheet" type="text/css" href="register.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
</head>
<body>
    <div class="wrapper">
        <form action="register.php" method="POST" id="registerForm" onsubmit="return validateForm()">
    
            <h2>Hala bizi keşfetmedin mi?<br>Kayıt Ol</h2>

            <div class="email-password">
                <input type="text" name="email" id="email" required>
                <label>E-mail</label>
                <span id="emailError" class="error"></span>
            </div>

            <div class="email-password">
                <input type="text" name="username" id="username" required>
                <label>Kullanıcı adı</label>
                <span id="usernameError" class="error"></span>
            </div>

            <div class="email-password">
                <input type="password" name="password" id="password" required>
                <label>Şifre</label>
            </div>

            <div class="email-password">
                <input type="text" name="phone" id="phone" maxlength="10" required>
                <label>Telefon numarası (Başında 0 olmadan)</label>
            </div>

            <div class="email-password">
                <input type="text" name="birthdate" id="birthdate" oninput="formatDate(this)" required>
                <label>Doğum tarihi</label>
            </div>

            <div class="login">
                <p>Zaten hesabın var mı? <a href="../login/login.php">Giriş Yap</a></p>
            </div>

            <button type="submit">Kayıt ol</button>


            <div id="phpErrorMessages" style="color: red; padding-top: 5px; text-align: center; font-weight: 100;">
    <?php 
        if (!empty($hata)) {
            echo $hata; 
        } 
    ?>
</div>

<div id="errorMessages">
</div>


        </form>
    </div>
</body>
</html>

<script src="register.js"></script>
