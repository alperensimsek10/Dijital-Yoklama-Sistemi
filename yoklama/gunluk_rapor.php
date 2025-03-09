<?php
// Veritabanı bağlantısı
$conn = new mysqli();// vrei tabanı bağlantısını yazınız.
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Karakter setini UTF-8 olarak ayarla
$conn->set_charset("utf8");

// Bugünün tarihini al ve formatla
$bugun = isset($_GET['bugun']) ? $_GET['bugun'] : date('Y-m-d');
$bugun_formatli = date('d/m/Y', strtotime($bugun));

// Bugünün yoklama verilerini al
$rapor_sorgu = "SELECT ogrenciler.ogrenciNo, ogrenciler.ogrenciAdi, yoklama.tarih, yoklama.durum, yoklama.ders_sayisi, yoklama.aciklama 
                FROM yoklama 
                JOIN ogrenciler ON yoklama.ogrenci_id = ogrenciler.ogrenci_id 
                WHERE yoklama.tarih = '$bugun' 
                ORDER BY yoklama.tarih DESC";
$sonuc = $conn->query($rapor_sorgu);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Günlük Yoklama Raporu</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-4"><mark><b>Günlük Yoklama Raporu - <?= htmlspecialchars($bugun_formatli) ?></b></mark></h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
            <tr>
                <th><b>Öğrenci No</b></th>
                <th><b>Öğrenci Adı</b></th>
                <th><b>Tarih</b></th>
                <th><b>Durum</b></th>
                <th><b>Ders Sayısı</b></th>
                <th><b>Açıklama</b></th>
            </tr>
            </thead>
            <tbody>
            <?php while ($rapor = $sonuc->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($rapor['ogrenciNo']) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['ogrenciAdi']) ?></b></td>
                    <td><b><?= htmlspecialchars(date('d/m/Y', strtotime($rapor['tarih']))) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['durum']) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['ders_sayisi']) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['aciklama']) ?></b></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap ve jQuery JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
