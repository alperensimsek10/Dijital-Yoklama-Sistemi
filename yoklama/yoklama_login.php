<?php
session_start();

// Eğer istemci otomatik olarak HTTPS'ye yönlendirilmişse, HTTP'ye geri yönlendir
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ogrenci_no = $_POST['ogrenci_no'];

    // Veritabanı bağlantısı
    $conn = new mysqli(); // veri tabanı bağlantısını yazınız.
    // Bağlantı hatası kontrolü
    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    // Öğrenciyi doğrula
    $stmt = $conn->prepare("SELECT * FROM ogrenciler WHERE ogrenciNo = ?");
    $stmt->bind_param("s", $ogrenci_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ogrenci = $result->fetch_assoc();
        $_SESSION['ogrenci_logged_in'] = true;
        $_SESSION['ogrenci_no'] = $ogrenci['ogrenciNo'];

        // HTTP protokolü ile yönlendirme
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/ogrenciDurum.php');
        exit();
    } else {
        $error = "Öğrenci numarası hatalı.";
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
    <title>Öğrenci Girişi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            width: 100px;
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
            background: #fff;
            border: none;
            outline: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            cursor: pointer;
            color: #000;
            margin-top: 10px;
            font-weight: bold;
        }

        .kutu:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, .5);
        }
    </style>
</head>
<body>

<div class="kutu">
    <img src="logo.png" alt="Logo">

    <h1>Öğrenci Girişi</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-box">
            <input type="text" name="ogrenci_no" id="ogrenci_no" placeholder="Öğrenci Numarası" required>
            <i class="fas fa-id-card"></i>
        </div>

        <div class="d-grid">
            <button type="submit" class="button">Giriş Yap</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
