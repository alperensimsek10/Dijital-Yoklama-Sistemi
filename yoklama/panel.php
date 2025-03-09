<?php
// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız.

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Karakter setini UTF-8 olarak ayarla
$conn->set_charset("utf8");

// Öğrencilerin her birinin toplam kaç derse katıldığını, kaç dersten izinli olduğunu, devamsızlık ve rapor durumunu sorgulama
$katilim_sorgu = "
    SELECT ogrenciler.ogrenciNo, ogrenciler.ogrenciAdi, 
           SUM(CASE WHEN yoklama.durum = 'katildi' THEN yoklama.ders_sayisi ELSE 0 END) AS toplam_katilim,
           
           -- Açıklamada 'izinli' veya 'İZİNLİ' geçiyorsa izinli ders sayısı
           SUM(CASE WHEN (yoklama.aciklama LIKE '%izinli%' OR yoklama.aciklama LIKE '%İZİNLİ%') THEN 
               (SELECT MAX(y2.ders_sayisi) 
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS izinli_ders_sayisi,
           
           -- Devamsızlık (açıklama boşsa veya 'İZİNLİ' ve 'RAPOR' içermiyorsa devamsızlık sayılır)
           SUM(CASE WHEN yoklama.durum = 'katilmadi' AND 
               (yoklama.aciklama IS NULL OR yoklama.aciklama = '' 
               OR (yoklama.aciklama NOT LIKE '%İZİNLİ%' AND yoklama.aciklama NOT LIKE '%RAPOR%')) THEN 
               (SELECT MAX(y2.ders_sayisi)
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS devamsizlik,
           
           -- Rapor (açıklamada 'RAPOR' veya 'RAPORLU' varsa raporlu ders sayılır)
           SUM(CASE WHEN yoklama.durum = 'katilmadi' AND 
               (yoklama.aciklama LIKE '%RAPOR%' OR yoklama.aciklama LIKE '%RAPORLU%') THEN 
               (SELECT MAX(y2.ders_sayisi)
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS rapor
    FROM ogrenciler 
    LEFT JOIN yoklama ON ogrenciler.ogrenci_id = yoklama.ogrenci_id
    GROUP BY ogrenciler.ogrenci_id
    ORDER BY ogrenciler.ogrenciNo ASC";

$sonuc = $conn->query($katilim_sorgu);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Devamsızlık</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .highlight {
            background-color: #ffcccc; /* Kırmızı arka plan */
        }
        table {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-4"><b><mark>Öğrencilerin Katılım, İzinli, Devamsızlık ve Rapor Durumları</b></mark></h2>
    <h6 style=color:red><b>Toplam 14 Hafta (42 Saat). Devamsızlık Oranı (%30)/13 Saat</b></h6>
    <h6><b><mark>NOT:</mark> Devamsızlık sayısı 9 ve üzeri olan öğrenciler kırmızı ile gösterilmiştir.</b></h6>
    <a href="rapor.php" class="btn btn-success">Tüm Kayıtlar</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
            <tr>
                <th><b>Öğrenci No</b></th>
                <th><b>Öğrenci Adı</b></th>
                <th><b>Toplam Katılım</b></th>
                <th><b>İzinli Olduğu Ders Sayısı</b></th>
                <th><b>Raporlu Olduğu Ders Sayısı</b></th>
                <th><b>Devamsızlık</b></th>
            </tr>
            </thead>
            <tbody>
            <?php while($ogrenci = $sonuc->fetch_assoc()): ?>

                <tr class="<?= $ogrenci['devamsizlik'] >= 9 ? 'highlight' : '' ?>">
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciNo']) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciAdi']) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['toplam_katilim'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['izinli_ders_sayisi'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['rapor'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['devamsizlik'] ?: 0) ?></b></td>
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
