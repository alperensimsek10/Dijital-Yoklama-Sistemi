
<?php
session_start();

// Eğer oturum açılmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['ogrenci_no'])) {
    header("Location: yoklama_login.php");
    exit();
}

$ogrenci_no = $_SESSION['ogrenci_no'];

// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Giriş yapan öğrencinin devamsızlık durumunu getiren sorgu (izinli dersler devamsızlıktan hariç tutuldu)
$katilim_sorgu = "
    SELECT ogrenciler.ogrenciNo, ogrenciler.ogrenciAdi, 
           SUM(CASE WHEN yoklama.durum = 'katildi' THEN yoklama.ders_sayisi ELSE 0 END) AS toplam_katilim,
           SUM(CASE WHEN (yoklama.aciklama LIKE '%izinli%' OR yoklama.aciklama LIKE '%İZİNLİ%') THEN 
               (SELECT MAX(y2.ders_sayisi) 
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS izinli_ders_sayisi,
           SUM(CASE WHEN yoklama.durum = 'katilmadi' 
               AND (yoklama.aciklama IS NULL OR yoklama.aciklama = '' 
                    OR (yoklama.aciklama NOT LIKE '%izinli%' AND yoklama.aciklama NOT LIKE '%RAPOR%')) 
               THEN 
               (SELECT MAX(y2.ders_sayisi)
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS devamsizlik,
           SUM(CASE WHEN yoklama.durum = 'katilmadi' 
               AND (yoklama.aciklama LIKE '%RAPOR%' OR yoklama.aciklama LIKE '%RAPORLU%') 
               THEN 
               (SELECT MAX(y2.ders_sayisi)
                FROM yoklama y2 
                WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
               ELSE 0 END) AS rapor
    FROM ogrenciler 
    LEFT JOIN yoklama ON ogrenciler.ogrenci_id = yoklama.ogrenci_id
    WHERE ogrenciler.ogrenciNo = ?
    GROUP BY ogrenciler.ogrenci_id
    ORDER BY ogrenciler.ogrenciNo ASC";

$stmt = $conn->prepare($katilim_sorgu);
$stmt->bind_param("s", $ogrenci_no);
$stmt->execute();
$sonuc = $stmt->get_result();
$ogrenci = $sonuc->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Öğrenci Devamsızlık Bilgisi</title>

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
        .table-container {
            overflow-x: auto; /* Mobilde yana kaydırılabilir */
        }
        @media (max-width: 768px) {
            h2 {
                font-size: 1.5rem;
            }
            h6 {
                font-size: 0.9rem;
            }
            .table {
                font-size: 0.85rem; /* Küçük ekranlarda yazı boyutu azalt */
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">

    <h3 class="mt-4 text-center"><b>BM302 - YAZILIM MÜHENDİSLİĞİ</b></h2>
    <h3 class="mt-4 text-center"><b>Devamsızlık Durumu</b></h2>
    <h6 class="text-danger text-center"><b>Toplam 14 Hafta (42 Saat). Devamsızlık Oranı (%30)/13 Saat</b></h6>
    <h6 class="text-center"><b><mark>NOT:</mark> Devamsızlık sayınız 9 ve üzerindeyse kırmızı ile gösterilmiştir.</b></h6>

    <div class="table-container">
        <table class="table table-bordered table-hover text-center">
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
            <?php if ($ogrenci): ?>
                <tr class="<?= $ogrenci['devamsizlik'] >= 9 ? 'highlight' : '' ?>">
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciNo']) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciAdi']) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['toplam_katilim'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['izinli_ders_sayisi'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['rapor'] ?: 0) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['devamsizlik'] ?: 0) ?></b></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center"><b>Kayıt bulunamadı.</b></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Uyarı Notu -->
<div class="alert alert-danger text-center mt-3">
    <b>Bu sayfa bilgilendirme amaçlıdır.</b> <br>
    <b>Kesin bilgi için Dersin Sorumlusu {öğretmen  unvan ad soyad} ile irtibata geçiniz.</b>
</div>
</div>

<!-- Bootstrap ve jQuery JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
