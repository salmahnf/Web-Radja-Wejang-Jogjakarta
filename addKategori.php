<?php 
include "koneksi.php"; // Hubungkan dengan file koneksi.php

// Fungsi untuk menambah kategori baru
if (isset($_POST['addKategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);

    // Cek apakah kategori sudah ada
    $query_cek = "SELECT * FROM kategori WHERE nama_kategori = ?";
    $stmt_cek = mysqli_prepare($konek, $query_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $nama_kategori);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $message = "Kategori sudah terdaftar!";
    } else {
        $query_insert = "INSERT INTO kategori (nama_kategori) VALUES (?)";
        $stmt_insert = mysqli_prepare($konek, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "s", $nama_kategori);
        mysqli_stmt_execute($stmt_insert);
        $message = "Kategori berhasil ditambahkan!";
    }
}

// Fungsi untuk menghapus kategori dan produk terkait
if (isset($_GET['delete'])) {
    $id_kategori = intval($_GET['delete']);

    // 1. Ambil semua id_produk yang terkait dengan kategori
    $query_produk = "SELECT id_produk FROM produk WHERE id_kategori = ?";
    $stmt_produk = mysqli_prepare($konek, $query_produk);
    mysqli_stmt_bind_param($stmt_produk, "i", $id_kategori);
    mysqli_stmt_execute($stmt_produk);
    $result_produk = mysqli_stmt_get_result($stmt_produk);
    $produk_list = mysqli_fetch_all($result_produk, MYSQLI_ASSOC);

    foreach ($produk_list as $produk) {
        $id_produk = $produk['id_produk'];

        // 2. Ambil semua varian yang terkait dengan produk ini
        $query_varian = "SELECT id_varian FROM produk_varian WHERE id_produk = ?";
        $stmt_varian = mysqli_prepare($konek, $query_varian);
        mysqli_stmt_bind_param($stmt_varian, "i", $id_produk);
        mysqli_stmt_execute($stmt_varian);
        $result_varian = mysqli_stmt_get_result($stmt_varian);
        $varian_list = mysqli_fetch_all($result_varian, MYSQLI_ASSOC);

        // 3. Hapus entri di tabel `produk_varian` untuk produk ini
        $query_hapus_produk_varian = "DELETE FROM produk_varian WHERE id_produk = ?";
        $stmt_hapus_produk_varian = mysqli_prepare($konek, $query_hapus_produk_varian);
        mysqli_stmt_bind_param($stmt_hapus_produk_varian, "i", $id_produk);
        mysqli_stmt_execute($stmt_hapus_produk_varian);

        // 4. Hapus produk itu sendiri
        $query_hapus_produk = "DELETE FROM produk WHERE id_produk = ?";
        $stmt_hapus_produk = mysqli_prepare($konek, $query_hapus_produk);
        mysqli_stmt_bind_param($stmt_hapus_produk, "i", $id_produk);
        mysqli_stmt_execute($stmt_hapus_produk);

        // 5. Cek apakah varian masih digunakan oleh produk lain
        foreach ($varian_list as $varian) {
            $id_varian = $varian['id_varian'];
            
            $query_cek_varian = "SELECT COUNT(*) as jumlah FROM produk_varian WHERE id_varian = ?";
            $stmt_cek_varian = mysqli_prepare($konek, $query_cek_varian);
            mysqli_stmt_bind_param($stmt_cek_varian, "i", $id_varian);
            mysqli_stmt_execute($stmt_cek_varian);
            $result_cek_varian = mysqli_stmt_get_result($stmt_cek_varian);
            $row = mysqli_fetch_assoc($result_cek_varian);

            // Hapus varian jika tidak digunakan oleh produk lain
            if ($row['jumlah'] == 0) {
                $query_hapus_varian = "DELETE FROM varian WHERE id_varian = ?";
                $stmt_hapus_varian = mysqli_prepare($konek, $query_hapus_varian);
                mysqli_stmt_bind_param($stmt_hapus_varian, "i", $id_varian);
                mysqli_stmt_execute($stmt_hapus_varian);
            }
        }
    }

    // 6. Hapus kategori itu sendiri
    $query_hapus_kategori = "DELETE FROM kategori WHERE id_kategori = ?";
    $stmt_hapus_kategori = mysqli_prepare($konek, $query_hapus_kategori);
    mysqli_stmt_bind_param($stmt_hapus_kategori, "i", $id_kategori);
    mysqli_stmt_execute($stmt_hapus_kategori);

    header("Location: addKategori.php");
    exit();
}

// Query untuk menampilkan semua kategori
$query_kategori = "SELECT * FROM kategori ORDER BY id_kategori";
$result_kategori = mysqli_query($konek, $query_kategori);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori</title>
    <link rel="stylesheet" href="styles2.css">
</head>
<body>
<div class="container">
    <!-- Tombol kembali ke halaman admin -->
    <div style="margin-bottom: 20px;">
        <button class="keranjang-button" onclick="window.location.href='halaman_admin.php';">Back</button>
    </div>

    <h2>Kategori</h2>
    
    <!-- Form untuk menambahkan kategori baru -->
    <form method="POST" action="">
        <input type="text" name="nama_kategori" placeholder="Nama Kategori" required>
        <button type="submit" name="addKategori">Add</button>
    </form>
    <?php if (isset($message)) { echo "<p style='color: red;'>$message</p>"; } ?>

    <!-- Tabel untuk menampilkan kategori -->
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
        <?php while ($kategori = mysqli_fetch_assoc($result_kategori)) { ?>
        <tr>
            <td><?= $kategori['nama_kategori']; ?></td>
            <td>
                <!-- Tombol Edit -->
                <button onclick="showEditForm(<?= $kategori['id_kategori']; ?>, '<?= $kategori['nama_kategori']; ?>')">Edit</button>
                
                <!-- Tombol Hapus -->
                <a href="addKategori.php?delete=<?= $kategori['id_kategori']; ?>" onclick="return confirm('Yakin ingin menghapus kategori ini? Semua produk terkait akan dihapus!')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Form untuk mengedit kategori -->
    <div id="editForm" style="display: none;">
        <form method="POST" action="">
            <input type="hidden" id="id_kategori" name="id_kategori">
            <input type="text" id="edit_nama_kategori" name="nama_kategori" required>
            <button type="submit" name="editKategori">Save</button>
            <button type="button" onclick="hideEditForm()">Cancel</button>
        </form>
    </div>
</div>

<script>
function showEditForm(id, nama) {
    document.getElementById('editForm').style.display = 'block';
    document.getElementById('id_kategori').value = id;
    document.getElementById('edit_nama_kategori').value = nama;
}

function hideEditForm() {
    document.getElementById('editForm').style.display = 'none';
}
</script>
</body>
</html>
