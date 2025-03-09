<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız.

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Gelen parametreleri al
$ogrenci_id = $_GET['ogrenci_id'] ?? null;
$status = $_GET['status'] ?? null; // Varsayılan değer null
$dersSayisi = $_GET['dersSayisi'] ?? null;
$tarih = $_GET['tarih'] ?? date('Y-m-d');
$reason = $_GET['reason'] ?? '';

// Veritabanına ekleme yap
if ($status) {
    // Açıklama yoksa NULL olarak ayarla
    $reason = ($status === 'katilmadi' && $reason === '') ? null : $conn->real_escape_string($reason);

    $sql = "INSERT INTO devamsizliklar (ogrenci_id, durum, ders_sayisi, tarih, aciklama) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Durumun NULL kontrolü yaparak bağlama
    $stmt->bind_param("issss", $ogrenci_id, $status, $dersSayisi, $tarih, $reason);

    if ($stmt->execute()) {
        echo "Başarılı! Öğrenci durumu kaydedildi.";
    } else {
        echo "Hata: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>

