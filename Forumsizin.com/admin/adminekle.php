<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["admin_adi"]) && isset($_POST["email"]) && isset($_POST["sifre"])) {
        $admin_adi = $_POST["admin_adi"];
        $email = $_POST["email"];
        $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT);

        // Bağlantı kodları
        $conn = new mysqli("localhost", "root", "", "forumsizin");
        if ($conn->connect_error) {
            die("Bağlantı hatası: " . $conn->connect_error);
        }

        $sql = "INSERT INTO admin (admin_adi, admin_mail, sifre) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $admin_adi, $email, $sifre);

        if ($stmt->execute()) {
            echo "✅ Admin başarıyla eklendi.";
        } else {
            echo "❌ Hata: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "❗ Form verileri eksik. Lütfen form üzerinden gönderin.";
    }

} else {
    echo "❗ Lütfen bu sayfaya doğrudan değil, bir form aracılığıyla POST isteği gönderin.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Yeni Admin Ekle</h2>
    <form action="" method="post">
    <label for="admin_adi">Admin Adı:</label>
    <input type="text" name="admin_adi" required><br><br>

    <label for="email">E-posta:</label>
    <input type="email" name="email" required><br><br>

    <label for="sifre">Şifre:</label>
    <input type="password" name="sifre" required><br><br>

    <button type="submit">Admin Ekle</button>
</form>


</body>
</html>
