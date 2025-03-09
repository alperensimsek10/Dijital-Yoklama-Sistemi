<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Veritabanı bağlantısı
$conn = new mysqli(); // veri tabanı bağlantısını yazınız.
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$ogrenciler_sorgu = "SELECT * FROM ogrenciler ORDER BY ogrenciNo ASC";
$sonuc = $conn->query($ogrenciler_sorgu);
$bugun = date('Y-m-d');
$adSoyad = $_SESSION['ad_soyad'] ?? 'Misafir';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yoklama</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .red-icon {
            color: red;
        }
        .sidebar {
            height: 100%;
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #333;
            color: #fff;
            padding-top: 20px;
            transition: width 0.3s ease;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .sidebar a i {
            margin-right: 8px;
        }
        .sidebar a:hover {
            background-color: #575757;
            color: #fff;
        }
        .sidebar .text-center img {
            width: 150px;
            margin-bottom: 20px;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        .welcome-message {
            float: right;
            margin: 10px;
        }
        /* Modal stili */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        /* Mobil uyumluluk */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding-top: 10px;
            }
            .sidebar a {
                font-size: 14px;
                justify-content: center;
            }
            .sidebar a i {
                margin: 0;
            }
            .sidebar .text-center img {
                width: 40px;
                height: auto;
                margin-bottom: 10px;
            }
            .content {
                margin-left: 70px;
                padding: 10px;
            }
            .welcome-message {
                font-size: 12px;
                margin: 5px;
            }
            .table-responsive table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="text-center">
        <img src="logo.png" alt="Logo">
    </div>
    <a href="rapor.php"><i class="fas fa-chart-bar"></i> <span><b>Genel Rapor</b></span></a>
    <a href="gunluk_rapor.php?bugun=<?= $bugun ?>"><i class="fas fa-calendar-day"></i> <span><b>Günlük Rapor</span></b></a>
    <a href="toplam_ders.php"><i class="fas fa-user-check"></i> <span><b>Toplam Katılım</span></b></a>
    <a href="excel.php"><i class="fas fa-file-excel"></i> <span><b>Excel</b></span></a>

</div>

<div class="content">
    <div class="welcome-message">
        <span>Hoş geldiniz, <b><?= htmlspecialchars($adSoyad) ?></b></span>
        <a href="login.php" class="btn btn-danger btn-sm">Çıkış</a>
    </div>
    <h2 class="mt-4"><b>BM302 - YAZILIM MÜHENDİSLİĞİ</b></h2>
    <h3>Öğrenci Yoklama Listesi</h3>
    <p><mark style=color:red><b>Toplam 14 Hafta (42 Saat). Devamsızlık Oranı (%30)/13 Saat</b></mark></p> // yoklama saatini kuralını yazınız

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="thead-dark">
            <tr>
                <th><b>Öğrenci No</b></th>
                <th><b>Öğrenci Adı</b></th>
                <th><b>Durum</b></th>
            </tr>
            </thead>
            <tbody>
            <?php while($ogrenci = $sonuc->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciNo']) ?></b></td>
                    <td><b><?= htmlspecialchars($ogrenci['ogrenciAdi']) ?></b></td>
                    <td><b>
                            <label for="ders_sayisi_<?= $ogrenci['ogrenci_id'] ?>">Ders sayısı:</label>
                            <select id="ders_sayisi_<?= $ogrenci['ogrenci_id'] ?>" class="form-control form-control-sm" onchange="updateLinks(<?= $ogrenci['ogrenci_id'] ?>)">
                                <option value="">Seçin</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                            <a id="katildi_<?= $ogrenci['ogrenci_id'] ?>" class="action-icons" href="#" onclick="setStatus(<?= $ogrenci['ogrenci_id'] ?>, 'katildi')">&#x2714;</a>

                            <a id="katilmadi_<?= $ogrenci['ogrenci_id'] ?>" class="action-icons red-icon" href="#" onclick="openModal(<?= $ogrenci['ogrenci_id'] ?>)">&#x2716;</a>
                        </b></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="absenceModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Devamsızlık Açıklaması</h2>
            <p>Devamsızlık nedenini girin:</p>
            <textarea id="absenceReason" class="form-control" rows="4"></textarea><br>
            <button class="btn btn-primary" onclick="submitAbsence()">Kaydet</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    let currentStudentId = null;

    function updateLinks(ogrenciId) {
        const dersSayisiSelect = document.getElementById(`ders_sayisi_${ogrenciId}`);
        const selectedValue = dersSayisiSelect.value;

        const katildiLink = document.getElementById(`katildi_${ogrenciId}`);
        const katilmadiLink = document.getElementById(`katilmadi_${ogrenciId}`);

        if (selectedValue) {
            katildiLink.href = `yoklama.php?ogrenci_id=${ogrenciId}&durum=katildi&ders_sayisi=${selectedValue}`;
            katilmadiLink.href = `yoklama.php?ogrenci_id=${ogrenciId}&durum=katilmadi&ders_sayisi=${selectedValue}`;
        } else {
            katildiLink.href = '#';
            katilmadiLink.href = '#';
        }
    }

    function openModal(ogrenciId) {
        currentStudentId = ogrenciId;
        document.getElementById("absenceModal").style.display = "block";
    }

    function submitAbsence() {
        const absenceReason = document.getElementById("absenceReason").value;
        const dersSayisiSelect = document.getElementById(`ders_sayisi_${currentStudentId}`);
        const selectedValue = dersSayisiSelect.value || 0;

        const absenceUrl = `yoklama.php?ogrenci_id=${currentStudentId}&durum=katilmadi&ders_sayisi=${selectedValue}&aciklama=${encodeURIComponent(absenceReason)}`;
        window.location.href = absenceUrl;
    }

    document.querySelector(".close").onclick = function () {
        const dersSayisiSelect = document.getElementById(`ders_sayisi_${currentStudentId}`);
        dersSayisiSelect.value = "0";
        document.getElementById("absenceModal").style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target === document.getElementById("absenceModal")) {
            document.getElementById("absenceModal").style.display = "none";
        }
    }
</script>

</body>
</html>
