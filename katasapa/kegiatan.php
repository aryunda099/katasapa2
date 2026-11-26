<?php
//error_reporting(0);
session_start();

require_once 'config/database.php';
$db=new Database();
?>



 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - KATASAPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/kegiatan.css">
</head>
<body class="halaman-kegiatan">

    <!-- HEADER / NAVBAR -->
    <header class="navbar">
        <div class="logo-container">
            <img src="assets/img/logo_katasapa.jpg" alt="Logo Katasapa" class="logo-img">
            <div>
                <h1 class="logo-text">KATASAPA</h1>
                <p class="logo-subtext">Komunitas Teater dan Sastra Perwira</p>
            </div>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="tentang.php">Tentang Kami</a></li>
                <li><a href="kegiatan.php" class="active">Kegiatan</a></li>
                <li><a href="karya.php">Karya</a></li>
                <li><a href="dokumentasi.php">Dokumentasi</a></li>
            </ul>
        </nav>
        <div class="nav-btn">
            <a href="admin/login_user.php" class="btn-login">Login</a>
        </div>
    </header>

    <!-- BANNER HEADER HALAMAN -->
    <section class="page-header">
        <h1>Agenda & Kegiatan</h1>
        <p>Jadwal workshop, diskusi sastra, latihan rutin, dan pementasan teater oleh KATASAPA Purbalingga.</p>
    </section>
    <br>

    <!-- KONTEN UTAMA -->
    <main class="content-container">
        
        <!-- Filter Kategori -->
        <div class="filter-kegiatan">
            <button class="filter-btn active" onclick="filterKegiatan('Semua', this)">Semua</button>
            <button class="filter-btn" onclick="filterKegiatan('Workshop', this)">Workshop</button>
            <button class="filter-btn" onclick="filterKegiatan('Diskusi', this)">Diskusi</button>
            <button class="filter-btn" onclick="filterKegiatan('Pertunjukan', this)">Pertunjukan</button>
        </div>

        <!-- Grid Kartu Kegiatan -->
        <div class="kegiatan-grid" id="kegiatanGrid">
            
            <!-- Kegiatan 1 (ID: 1) -->
            
<?php
$data = $db->show("kegiatan2");

foreach ($data as $d) {

    // Mengubah format tanggal
    $tanggal = date('d', strtotime($d['tanggal_kegiatan']));

    $bulan = [
        1 => 'JAN', 'FEB', 'MAR', 'APR',
        'MEI', 'JUN', 'JUL', 'AGU',
        'SEP', 'OKT', 'NOV', 'DES'
    ];

    $nomor_bulan = (int) date('n', strtotime($d['tanggal_kegiatan']));
    $nama_bulan = $bulan[$nomor_bulan];

    // Format waktu
    $waktu_mulai = !empty($d['waktu_kegiatan'])
        ? date('H.i', strtotime($d['waktu_kegiatan']))
        : '';

    $waktu_selesai = !empty($d['waktu_selesai'])
        ? date('H.i', strtotime($d['waktu_selesai']))
        : 'Selesai';
?>

    <a href="detail_kegiatan.php?id=<?= $d['id_kegiatan'] ?>"
       class="card-kegiatan-link"
       data-kategori="<?= htmlspecialchars($d['kategori_kegiatan']) ?>">

        <article class="card-kegiatan">

            <div class="card-kegiatan-img"
                 style="background-image: url('<?= htmlspecialchars($d['poster']) ?>');">
            </div>

            <div class="card-kegiatan-body">

                <div class="tanggal-box">
                    <span class="tgl"><?= $tanggal ?></span>
                    <span class="bln"><?= $nama_bulan ?></span>
                </div>

                <div class="info-kegiatan">

                    <h3>
                        <?= htmlspecialchars($d['judul_kegiatan']) ?>
                    </h3>

                    <p class="lokasi">
                        📍 <?= htmlspecialchars($d['lokasi_kegiatan']) ?>
                    </p>

                    <p class="waktu">
                        🕒 <?=htmlspecialchars($d['waktu_kegiatan'])?>
                        WIB
                    </p>

                    <p class="deskripsi">
                        <?= htmlspecialchars($d['deskripsi_singkat']) ?>
                    </p>

                    <span class="badge-kategori">
                        <?= htmlspecialchars($d['kategori_kegiatan']) ?>
                    </span>

                </div>
            </div>
        </article>
    </a>

<?php
}
?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p><strong>KATASAPA</strong> © 2026 KATASAPA. Semua Hak Dilindungi.</p>
        <p>Instagram: <a href="https://instagram.com" target="_blank" rel="noopener noreferrer">@katasapa_pbg</a></p>
    </footer>

    <!-- Script Filter Sederhana -->
    <script>
        function filterKegiatan(kategori_semua, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter berdasarkan bungkus link kartu
            const links = document.querySelectorAll('.card-kegiatan-link');
            links.forEach(link => {
                if (kategori_semua === 'Semua' || link.getAttribute('data-kategori') === kategori_semua) {
                    link.style.display = 'block';
                } else {
                    link.style.display = 'none';
                }
            });
        }
    </script>

</body>
</html>