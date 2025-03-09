<?php
// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız.

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Karakter setini UTF-8 olarak ayarla
$conn->set_charset("utf8");

// Yoklama verilerini al
$rapor_sorgu = "SELECT ogrenciler.ogrenciNo, ogrenciler.ogrenciAdi, yoklama.tarih, yoklama.durum, yoklama.ders_sayisi, yoklama.aciklama 
                FROM yoklama 
                JOIN ogrenciler ON yoklama.ogrenci_id = ogrenciler.ogrenci_id 
                ORDER BY yoklama.tarih DESC";
$sonuc = $conn->query($rapor_sorgu);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoklama Raporu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .thead-dark {
            background-color: #000; /* Siyah arka plan */
            color: #fff; /* Beyaz metin */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4"><mark><b>Yoklama Raporu</b></mark></h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
            <tr>
                <th><mark>Öğrenci No<mark></th>
                <th><mark>Öğrenci Adı</mark></th>
                <th><mark>Tarih</mark></th>
                <th><mark>Durum</mark></th>
                <th><mark>Ders Sayısı</mark></th>
                <th><mark>Açıklama</mark></th>
            </tr>
            </thead>
            <tbody>
            <?php while($rapor = $sonuc->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($rapor['ogrenciNo']) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['ogrenciAdi']) ?></b></td>
                    <td><b><?= htmlspecialchars(date('d-m-Y', strtotime($rapor['tarih']))) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['durum']) ?></b></td>
                    <td><b><?= htmlspecialchars($rapor['ders_sayisi'])?></b></td>
                    <td>
                        <b>
                            <?= $rapor['aciklama'] !== null && $rapor['aciklama'] !== ''
                                ? '<mark>' . htmlspecialchars($rapor['aciklama']) . '</mark>'
                                : '' ?>
                        </b>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
