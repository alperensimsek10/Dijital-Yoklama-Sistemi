<?php
// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız.
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// GET ile gelen öğrenci_id, durum ve ders_sayisi verilerini al
if (isset($_GET['ogrenci_id']) && isset($_GET['durum'])) {
    $ogrenci_id = $_GET['ogrenci_id'];
    $durum = $_GET['durum'];

    // Yıl-Ay-Gün formatında bugünün tarihini al
    $tarih = date('Y-m-d');  // Format: 2024-09-25

    // Ders sayısını kontrol et (varsayılan 1 ders)
    $ders_sayisi = isset($_GET['ders_sayisi']) ? $_GET['ders_sayisi'] : 1;

    // Açıklama var mı kontrol et
    $aciklama = isset($_GET['aciklama']) ? $_GET['aciklama'] : null;

    // Yoklama kaydet
    $sorgu = $conn->prepare("INSERT INTO yoklama (ogrenci_id, tarih, durum, ders_sayisi, aciklama) VALUES (?, ?, ?, ?, ?)");
    $sorgu->bind_param("issis", $ogrenci_id, $tarih, $durum, $ders_sayisi, $aciklama);

    if ($sorgu->execute()) {
        echo "Yoklama başarıyla kaydedildi!";
    } else {
        echo "Yoklama kaydedilirken bir hata oluştu: " . $sorgu->error;
    }

    // Ana ekrana yönlendir
    header("Location: index.php");
    exit();
} else {
    echo "Eksik bilgi: ogrenci_id veya durum belirtilmedi.";
}
?>
