<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['admin'])) {
    header("location: login_user.php");
    exit;
}

require_once '../config/database.php';

$db = new Database();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KATASAPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="admin-body">

    <div class="admin-wrapper">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <h2>KATASAPA</h2>
                <span>Panel Admin Kesenian</span>
            </div>
            <nav class="sidebar-menu">
                <a href="kelola_karya.php" class="active">Kelola Karya Sastra</a>
                <a href="kelola_kegiatan.php">Kelola Kegiatan</a>
                <a href="kelola_dokumentasi.php">Dokumentasi</a>
                <a href="../karya.php" target="_blank" class="btn-lihat-web">Lihat Halaman Karya &nearr;</a>
                <a href="../index.php" class="btn-logout">Kembali ke Beranda</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1>Dashboard Pengelolaan Karya</h1>
                    <p>Tambah, ubah, atau hapus arsip puisi, cerpen, esai, dan naskah teater.</p>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Total Karya Terbit</span>
                    <span class="stat-number"><?= $total_karya; ?></span>
                </div>
            </header>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert-box">
                    <?php
                        if ($_GET['status'] == 'added') echo "Karya baru berhasil ditambahkan!";
                        if ($_GET['status'] == 'updated') echo "Perubahan karya berhasil disimpan!";
                        if ($_GET['status'] == 'deleted') echo "Karya berhasil dihapus dari database!";
                    ?>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH / EDIT (CREATE & UPDATE) -->
            <section class="admin-card">
                <h3 class="admin-card-title">
                    <?= $edit_data ? "Edit Karya: " . htmlspecialchars($edit_data['judul']) : "Tambah Karya Baru"; ?>
                </h3>
                
                <form action="index.php" method="POST" enctype="multipart/form-data" class="admin-form">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?= $edit_data['gambar']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Kategori Karya</label>
                            <select name="kategori" required>
                                <?php 
                                    $kategori_opsi = ['PUISI', 'CERPEN', 'ESAI SASTRA', 'NASKAH TEATER'];
                                    foreach ($kategori_opsi as $kat) {
                                        $selected = ($edit_data && $edit_data['kategori'] == $kat) ? 'selected' : '';
                                        echo "<option value='$kat' $selected>$kat</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Judul Karya</label>
                            <input type="text" name="judul" placeholder="Contoh: Hujan di Alun-Alun Perwira" 
                                   value="<?= $edit_data ? htmlspecialchars($edit_data['judul']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Penulis / Divisi</label>
                            <input type="text" name="penulis" placeholder="Contoh: Anggota Katasapa" 
                                   value="<?= $edit_data ? htmlspecialchars($edit_data['penulis']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Bulan & Tahun Terbit</label>
                            <input type="text" name="waktu_publikasi" placeholder="Contoh: Nov 2026" 
                                   value="<?= $edit_data ? htmlspecialchars($edit_data['waktu_publikasi']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cuplikan / Isi Singkat Karya</label>
                        <textarea name="cuplikan" rows="4" placeholder="Tuliskan bait puisi atau paragraf pembuka..." required><?= $edit_data ? htmlspecialchars($edit_data['cuplikan']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Latar Kartu (Opsional)</label>
                        <input type="file" name="gambar" accept="image/*">
                        <?php if ($edit_data && !empty($edit_data['gambar'])): ?>
                            <small class="help-text">Gambar saat ini: <?= htmlspecialchars($edit_data['gambar']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary-gold">
                            <?= $edit_data ? "Simpan Perubahan" : "+ Publikasikan Karya"; ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="index.php" class="btn-secondary-dark">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <!-- TABEL DAFTAR KARYA (READ & DELETE) -->
            <section class="admin-card">
                <h3 class="admin-card-title">Daftar Karya Terpublikasi</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Judul & Cuplikan</th>
                                <th>Penulis</th>
                                <th>Waktu</th>
                                <th>Aksi (CRUD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($total_karya > 0):
                                while ($row = mysqli_fetch_assoc($data_karya)): 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge-kategori"><?= htmlspecialchars($row['kategori']); ?></span></td>
                                <td>
                                    <strong class="table-judul"><?= htmlspecialchars($row['judul']); ?></strong>
                                    <p class="table-cuplikan"><?= htmlspecialchars(substr($row['cuplikan'], 0, 85)); ?>...</p>
                                </td>
                                <td><?= htmlspecialchars($row['penulis']); ?></td>
                                <td><?= htmlspecialchars($row['waktu_publikasi']); ?></td>
                                <td class="aksi-buttons">
                                    <a href="index.php?edit=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="index.php?hapus=<?= $row['id']; ?>" 
                                       class="btn-hapus" 
                                       onclick="return confirm('Yakin ingin menghapus karya \'<?= htmlspecialchars($row['judul']); ?>\'?')">Hapus</a>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 30px; color: #888;">Belum ada data karya. Silakan tambahkan melalui form di atas.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>
</html>