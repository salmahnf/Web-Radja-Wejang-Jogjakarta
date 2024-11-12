<?php 
include "koneksi.php"; 

// Ambil ID produk dari URL
$id_produk = isset($_GET['id']) ? $_GET['id'] : 0;

// Query untuk mendapatkan data produk berdasarkan ID
$query_produk = "SELECT * FROM produk WHERE id_produk = ?";
$stmt_produk = mysqli_prepare($konek, $query_produk);
mysqli_stmt_bind_param($stmt_produk, "i", $id_produk);
mysqli_stmt_execute($stmt_produk);
$result_produk = mysqli_stmt_get_result($stmt_produk);
$produk = mysqli_fetch_assoc($result_produk);

// Jika produk tidak ditemukan, hentikan eksekusi
if (!$produk) {
    echo "Produk tidak ditemukan!";
    exit;
}

// Query untuk mengambil semua kategori
$query_kategori = "SELECT * FROM kategori";
$result_kategori = mysqli_query($konek, $query_kategori);

// Query untuk mengambil varian terkait produk ini
$query_varian = "SELECT pv.id_produk_varian, pv.stok, v.id_varian, v.nama_varian 
                 FROM produk_varian pv 
                 INNER JOIN varian v ON pv.id_varian = v.id_varian 
                 WHERE pv.id_produk = ?";
$stmt_varian = mysqli_prepare($konek, $query_varian);
mysqli_stmt_bind_param($stmt_varian, "i", $id_produk);
mysqli_stmt_execute($stmt_varian);
$result_varian = mysqli_stmt_get_result($stmt_varian);
$varian_list = mysqli_fetch_all($result_varian, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="styles2.css">
    <script>
        function addVarianInput() {
            const container = document.getElementById("newVarianContainer");
            const varianDiv = document.createElement("div");

            // Input untuk nama varian
            const inputNamaVarian = document.createElement("input");
            inputNamaVarian.type = "text";
            inputNamaVarian.name = "new_nama_varian[]";
            inputNamaVarian.placeholder = "Nama Varian Baru";
            inputNamaVarian.required = true;

            // Input untuk stok varian
            const inputStokVarian = document.createElement("input");
            inputStokVarian.type = "text";
            inputStokVarian.name = "new_stok[]";
            inputStokVarian.placeholder = "Stok Baru";
            inputStokVarian.required = true;
            inputStokVarian.oninput = function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            };

            varianDiv.appendChild(inputNamaVarian);
            varianDiv.appendChild(inputStokVarian);
            container.appendChild(varianDiv);
        }

        function removeVarian(element) {
            element.parentElement.style.display = 'none';
            element.parentElement.querySelector("input[name='delete_varian[]']").value = '1';
        }
    </script>
</head>
<body style="background-image: url('assets/checkoutBg.png'); background-size: cover;">
<div class="container">

    <h2>Edit Produk</h2>
    <form method="post" action="editOleh_proses.php" enctype="multipart/form-data">
        <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">

        <!-- Pilih Kategori -->
        <label>Kategori:</label>
        <select name="kategori" required>
            <?php while ($kategori = mysqli_fetch_assoc($result_kategori)) { ?>
                <option value="<?= $kategori['id_kategori']; ?>" <?= ($kategori['id_kategori'] == $produk['id_kategori']) ? 'selected' : ''; ?>>
                    <?= $kategori['nama_kategori']; ?>
                </option>
            <?php } ?>
        </select>
        <br>

        <!-- Input Nama Produk -->
        <label for="nama">Nama Produk:</label>
        <input type="text" name="nama" id="nama" value="<?= $produk['nama_produk']; ?>" required>
        <br>

        <!-- Input Isi Produk -->
        <label for="isi">Isi Produk:</label>
        <input type="text" name="isi" id="isi" value="<?= $produk['isi']; ?>" required>
        <br>

        <!-- Input Harga Produk -->
        <label for="harga">Harga Produk:</label>
        <input type="text" name="harga" id="harga" value="<?= $produk['harga']; ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <br>

        <!-- Upload Gambar Produk -->
        <label for="gambar">Gambar Produk (biarkan kosong jika tidak ingin mengubah):</label>
        <input type="file" name="gambar" id="gambar" accept="image/*">
        <br>
        <img src="data:image/jpeg;base64,<?= base64_encode($produk['foto']); ?>" alt="Produk" style="width: 150px;">
        <br>

        <!-- Input Varian Produk -->
        <h3>Varian Produk:</h3>
        <?php foreach ($varian_list as $index => $varian) { ?>
            <div>
                <input type="hidden" name="id_produk_varian[]" value="<?= $varian['id_produk_varian']; ?>">
                <input type="hidden" name="old_id_varian[]" value="<?= $varian['id_varian']; ?>">
                <input type="hidden" name="delete_varian[]" value="0"> <!-- Hidden field untuk penanda hapus -->
                
                <label>Nama Varian:</label>
                <input type="text" name="nama_varian[]" value="<?= $varian['nama_varian']; ?>">

                <label>Stok:</label>
                <input type="text" name="stok[]" value="<?= $varian['stok']; ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                
                <!-- Tombol hapus varian -->
                <button type="button" onclick="removeVarian(this)">Hapus Varian</button>
            </div>
        <?php } ?>

        <!-- Tambah Varian Baru -->
        <div id="newVarianContainer"></div>
        <button type="button" onclick="addVarianInput()">Tambah Varian</button>
        <br>

        <!-- Tombol Simpan -->
        <button type="submit">Simpan Perubahan</button>
        <button type="button" onclick="window.location.href='halaman_admin.php';">Kembali</button>
    </form>
</div>
</body>
</html>
