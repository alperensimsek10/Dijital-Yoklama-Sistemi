<?php
// Veritabanı bağlantısı (doğru bilgilerle düzenlendi)
$conn = new mysqli(); // veri tabanı bağlantısını yazınız
// Bağlantı hatası varsa mesaj göster
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Veritabanı bağlantısında Türkçe karakter sorunlarını çözmek için UTF-8 set ediliyor
$conn->set_charset("utf8");

// Öğrencilerin her birinin toplam kaç derse katıldığını, izinli olduğu, devamsızlık ve rapor durumunu sorgulama
$katilim_sorgu = "
    SELECT 
        ogrenciler.ogrenciNo, 
        ogrenciler.ogrenciAdi,
        SUM(CASE WHEN yoklama.durum = 'katildi' THEN yoklama.ders_sayisi ELSE 0 END) AS toplam_katilim,

        -- İzinli ders sayısı
        SUM(CASE WHEN (yoklama.aciklama LIKE '%izinli%' OR yoklama.aciklama LIKE '%İZİNLİ%') THEN 
            (SELECT MAX(y2.ders_sayisi) 
             FROM yoklama y2 
             WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
             ELSE 0 END) AS izinli_ders_sayisi,

        -- Raporlu ders sayısı
        SUM(CASE WHEN yoklama.durum = 'katilmadi' AND 
            (yoklama.aciklama LIKE '%RAPOR%' OR yoklama.aciklama LIKE '%RAPORLU%') THEN 
            (SELECT MAX(y2.ders_sayisi) 
             FROM yoklama y2 
             WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
             ELSE 0 END) AS rapor,

        -- Devamsızlık hesaplaması: açıklama kısmında İZİNLİ veya RAPORLU YOKSA devamsız say
        SUM(CASE WHEN yoklama.durum = 'katilmadi' AND 
            (yoklama.aciklama IS NULL OR yoklama.aciklama = '' 
             OR (yoklama.aciklama NOT LIKE '%izinli%' AND yoklama.aciklama NOT LIKE '%İZİNLİ%' 
                 AND yoklama.aciklama NOT LIKE '%RAPOR%' AND yoklama.aciklama NOT LIKE '%RAPORLU%')) THEN 
            (SELECT MAX(y2.ders_sayisi) 
             FROM yoklama y2 
             WHERE y2.tarih = yoklama.tarih AND y2.durum = 'katildi') 
             ELSE 0 END) AS devamsizlik

    FROM ogrenciler 
    LEFT JOIN yoklama ON ogrenciler.ogrenci_id = yoklama.ogrenci_id 
    GROUP BY ogrenciler.ogrenci_id 
    ORDER BY ogrenciler.ogrenciNo ASC
";

$sonuc = $conn->query($katilim_sorgu);

// Excel dosyasını oluşturma
header('Content-Encoding: UTF-8');
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Yazilim_Muhendisligi_Yoklama_Listesi.xls"');
header('Cache-Control: max-age=0');

// UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

// Başlık Bilgisi
echo '<h2>BM302 - YAZILIM MÜHENDİSLİĞİ</h2>';  // ders kodunu - adını yazınız

// Sütun Başlıkları
echo '<table border="1">';
echo '<tr><th>Öğrenci No</th><th>Öğrenci Adı</th><th>Katıldığı Ders Sayısı</th><th>İzinli Olduğu Ders Sayısı</th><th>Rapor</th><th>Devamsızlık</th></tr>';

// Öğrencilerin katılım, izinli ders, devamsızlık ve rapor sayılarını yazdırma
while ($ogrenci = $sonuc->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($ogrenci['ogrenciNo']) . '</td>';
    echo '<td>' . htmlspecialchars($ogrenci['ogrenciAdi']) . '</td>';
    echo '<td>' . htmlspecialchars($ogrenci['toplam_katilim'] ?: 0) . '</td>'; // Eğer hiç katılmadıysa 0 göster
    echo '<td>' . htmlspecialchars($ogrenci['izinli_ders_sayisi'] ?: 0) . '</td>'; // İzinli ders sayısını ekleme
    echo '<td>' . htmlspecialchars($ogrenci['rapor'] ?: 0) . '</td>'; // Rapor sayısını ekleme

    // Devamsızlık hücresini sarı fosforlu ile boyama
    echo '<td style="background-color: #ffff00;">' . htmlspecialchars($ogrenci['devamsizlik'] ?: 0) . '</td>'; // Devamsızlık sayısını ekleme

    echo '</tr>';
}

echo '</table>';
?>
