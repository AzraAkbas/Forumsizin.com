<?php

session_start();
$cookiesAccepted = false;
if (isset($_COOKIE["cookies_accepted"]) && $_COOKIE["cookies_accepted"] === "true") {
  $cookiesAccepted = true;
}


if (isset($_COOKIE["email"])) {
  $_SESSION["email"] = $_COOKIE["email"];
  header("Location: ../home page/home_page.php");
  exit();
}

$hata = "";


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
  $email = $_POST["email"];
  $sifre = $_POST["password"];

  $remember = isset($_POST["remember"]);

  if ($remember && !$cookiesAccepted) {
    $hata = "'Beni Hatırla' özelliğini kullanabilmek için lütfen çerezleri kabul edin.";
  } else {
    $sql = "SELECT * FROM kullanici WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $sonuc = $stmt->get_result();

    if ($sonuc->num_rows === 1) {
      $kullanici = $sonuc->fetch_assoc();

      if (password_verify($sifre, $kullanici['sifre'])) {
        $_SESSION["email"] = $email;

        if ($remember && $cookiesAccepted) {
          setcookie("email", $email, time() + (30 * 24 * 60 * 60), "/");
        }

        header("Location: ../home page/home_page.php");
        exit();
      } else {
        $hata = "Geçersiz şifre.";
      }
    } else {
      $hata = "Bu e-posta ile kayıtlı kullanıcı yok.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giriş Yap</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div id="cookie-banner" style="display: none;">
    <p>Bu site deneyiminizi iyileştirmek için çerezler kullanır.</p>
    <button class="cookie-button" name="accept">Kabul Et</button>
    <button class="cookie-button" name="reject">Reddet</button>
  </div>

  <form action="login.php" method="POST" onsubmit="return checkRememberAndCookies();">
    <div class="header">
      <h1>Merhaba, biz de seni bekliyorduk!</h1>
    </div>
    <div class="wrapper">
      <div class="image">
        <img src="https://i.pinimg.com/736x/79/67/08/796708c422998b8eadf3241dbb80703f.jpg" alt="">
      </div>
      <div class="input">
        <div class="email-password">
          <input type="text" name="email" id="email" required>
          <label for="email">E-mail</label>
        </div>
        <div class="email-password">
          <input type="password" name="password" id="password" required>
          <label for="password">Şifre</label>
        </div>
        <div class="remember">
          <label>
            <input type="checkbox" name="remember" id="remember">
            <p>Beni Hatırla</p>
          </label>
          <a href=" ../forget_password/forget_password.php" class="forgot-password">Şifrenizi mi unuttunuz?</a>
        </div>
        <button class="login-button" type="submit">Giriş Yap</button>
        <div class="register">
          <p>Bir hesabınız yok mu? <a href=" ../register/register.php">Kayıt Ol</a></p>
        </div>
        <?php if (!empty($hata)) echo "<p style='color:red;'>$hata</p>"; ?>
      </div>
    </div>
  </form>
  
  <script src="login.js"></script>
</body>
</html>

