<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    // Veritabanı bağlantısı
    $conn = new mysqli(); // veri tabanı bağlantısını yazınız.

    // Bağlantı hatası kontrolü
    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    // Kullanıcıyı doğrula
    $stmt = $conn->prepare("SELECT * FROM adminler WHERE kullanici_adi = ? AND sifre = ?");
    $stmt->bind_param("ss", $kullanici_adi, $sifre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['kullanici_adi'] = $admin['kullanici_adi'];
        $_SESSION['ad_soyad'] = $admin['ad_soyad']; // Oturuma ad_soyad'ı ekle

        header('Location: index.php');
        exit();
    } else {
        $error = "Kullanıcı adı veya şifre yanlış.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazılım Mühendisliği</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="shortcut icon" href="https://www.artvin.edu.tr/assets/themes/mint/assets/images/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('Main.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }

        .kutu {
            width: 420px;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, .2);
            color: #fff;
            border-radius: 15px;
            padding: 30px 40px;
            backdrop-filter: blur(30px);
            box-shadow: 0 0 10px rgba(0, 0, 0, .2);
        }

        .kutu img {
            display: block;
            margin: 0 auto;
            width: 100px; /* Logonun genişliğini ayarladık */
        }

        .kutu h1 {
            font-size: 25px;
            text-align: center;
        }

        .input-box {
            position: relative;
            width: 100%;
            height: 50px;
            margin: 30px 0;
        }

        .input-box input {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            outline: none;
            border: 2px solid rgba(255, 255, 255, .2);
            border-radius: 40px;
            font-size: 16px;
            color: #fff;
            padding: 20px 40px 20px 20px;
        }

        .input-box input::placeholder {
            color: #fff;
        }

        .input-box i {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
        }

        .kutu .button {
            width: 100%;
            height: 45px;
            background: #fff; /* Buton arka planı beyaz */
            border: none;
            outline: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            cursor: pointer;
            color: #000; /* Buton yazısı siyah */
            margin-top: 10px;
            font-weight: bold; /* Yazıyı daha belirgin yapmak için */
        }

        .kutu:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, .5);
        }
    </style>
</head>
<body>

<div class="kutu">
    <!-- Logo Ekleme -->
    <img src="logo.png" alt="Logo">

    <h1>Giriş Ekranı</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-box">
            <input type="text" name="kullanici_adi" id="kullanici_adi" placeholder="Kullanıcı Adı" required>
            <i class="fas fa-user"></i> <!-- Kullanıcı ikonu -->
        </div>

        <div class="input-box">
            <input type="password" name="sifre" id="sifre" placeholder="Şifre" required>
            <i class="fas fa-lock"></i> <!-- Kilit ikonu -->
        </div>

        <div class="d-grid">
            <button type="submit" class="button">Giriş Yap</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
