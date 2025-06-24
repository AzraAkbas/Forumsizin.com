<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forumsizin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

  
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçersiz e-posta adresi.";
    } else {

        $sql = "SELECT * FROM kullanici WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
  
            $success_message = "E-posta adresinize şifre sıfırlama bağlantısı gönderilmiştir.";
        } else {

            $error_message = "Bu e-posta adresi sistemde kayıtlı değil.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="forget_password.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifreyi Unuttum</title>
</head>

<body>

    <div class="wrapper">
        <form action="" method="POST">
            <h2>Şifreni mi Unuttun?</h2>
            <?php
            if (isset($error_message)) {
                echo "<p style='color:red;'>$error_message</p>";
            }
            if (isset($success_message)) {
                echo "<p style='color:green;'>$success_message</p>";
            }
            ?>
            <div class="email">
                <input type="text" name="email" required>
                <label for="email">E-mail</label>
            </div>
            <div class="login">
                <p>Şifreni hatırlıyor musun? <a href="../login/login.php">Giriş Yap</a></p>
            </div>
            <button type="submit">Şifreni Sıfırla</button>
        </form>
    </div>
</body>

</html>
