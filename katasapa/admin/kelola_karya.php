<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['admin'])) {
    header("location: login_user.php");
    exit;
}

require_once '../config/database.php';

$db = new Database();
$edit_data = null;

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $lama = $db->get('karya', ['id' => $id]);

    if ($lama && !empty($lama['gambar']) && $lama['gambar'] != 'img/unwiku_katasapa.webp') {
        $file_path = "../assets/" . $lama['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $db->del('karya', ['id' => $id]);
    header("Location: kelola_karya.php?status=deleted");
    exit;
}

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_data = $db->get('karya', ['id' => $id]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gambar_db = isset($_POST['gambar_lama']) ? $_POST['gambar_lama'] : '';

    if (!empty($_FILES['gambar']['name'])) {
        $nama_file = time() . "_" . basename($_FILES['gambar']['name']);
        $target_dir = "../assets/img/" . $nama_file;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir)) {
            $gambar_db = "img/" . $nama_file;
        }
    }

    $payload = [
        'kategori'        => $_POST['kategori'],
        'judul'           => $_POST['judul'],
        'cuplikan'        => $_POST['cuplikan'],
        'penulis'         => $_POST['penulis'],
        'waktu_publikasi' => $_POST['waktu_publikasi'],
        'gambar'          => $gambar_db
    ];

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $db->save('karya', $payload, ['id' => $id]);
        header("Location: kelola_karya.php?status=updated");
    } else {
        $db->insert('karya', $payload);
        header("Location: kelola_karya.php?status=added");
    }
    exit;
}

$data_karya  = $db->sql("SELECT * FROM karya ORDER BY id DESC");
$total_karya = count($data_karya);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karya - KATASAPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="admin-body">

    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1>Kelola Karya Sastra</h1>
                    <p>Tambah, ubah, atau hapus arsip puisi, cerpen, esai, dan naskah teater.</p>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Total Karya Terbit</span>
                    <span class="stat-number"><?= $total_karya; ?></span>
                </div>
            </header>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert-box">
                    <?php
                        if ($_GET['status'] == 'added') echo "Karya baru berhasil ditambahkan!";
                        if ($_GET['status'] == 'updated') echo "Perubahan karya berhasil disimpan!";
                        if ($_GET['status'] == 'deleted') echo "Karya berhasil dihapus dari database!";
                    ?>
                </div>
            <?php endif; ?>

            <section class="admin-card">
                <h3 class="admin-card-title">
                    <?= $edit_data ? "Edit Karya: " . htmlspecialchars($edit_data['judul']) : "Tambah Karya Baru"; ?>
                </h3>

                <form action="kelola_karya.php" method="POST" enctype="multipart/form-data" class="admin-form">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($edit_data['gambar']); ?>">
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
                            <a href="kelola_karya.php" class="btn-secondary-dark">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

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
                                foreach ($data_karya as $row):
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge-kategori"><?= htmlspecialchars($row['kategori']); ?></span></td>
                                <td>
                                    <strong class="table-judul"><?= htmlspecialchars($row['judul']); ?></strong>
                                    <p class="table-cuplikan"><?= htmlspecialchars(mb_substr($row['cuplikan'], 0, 85)); ?>...</p>
                                </td>
                                <td><?= htmlspecialchars($row['penulis']); ?></td>
                                <td><?= htmlspecialchars($row['waktu_publikasi']); ?></td>
                                <td class="aksi-buttons">
                                    <a href="kelola_karya.php?edit=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="kelola_karya.php?hapus=<?= $row['id']; ?>"
                                       class="btn-hapus"
                                       onclick="return confirm('Yakin ingin menghapus karya \'<?= htmlspecialchars($row['judul'], ENT_QUOTES); ?>\'?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                endforeach;
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