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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Edit Produk</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="edit.css">
  <script>
    function addVarianInput() {
      const container = document.getElementById("newVarianContainer");
      const varianDiv = document.createElement("div");
      varianDiv.classList.add("form-group-row");
      varianDiv.style.marginBottom = "20px"; // Jarak antar varian

      // Label untuk Nama Varian
      const labelVarian = document.createElement("label");
      labelVarian.setAttribute("for", "nama-varian");
      labelVarian.innerText = "Varian Produk";
      labelVarian.style.marginLeft = "0px";
      labelVarian.style.width = "150px"; // Penyesuaian sesuai dengan label yang ada

      // Input untuk nama varian
      const inputNamaVarian = document.createElement("input");
      inputNamaVarian.type = "text";
      inputNamaVarian.name = "new_nama_varian[]";
      inputNamaVarian.placeholder = "Nama Varian Baru";
      inputNamaVarian.required = true;
      inputNamaVarian.style.width = "100%"; // Sesuaikan dengan lebar input lainnya

      // Label untuk Stok Varian
      const labelStok = document.createElement("label");
      labelStok.setAttribute("for", "stok-varian");
      labelStok.innerText = "Stok Produk";

      // Input untuk stok varian
      const inputStokVarian = document.createElement("input");
      inputStokVarian.type = "text";
      inputStokVarian.name = "new_stok[]";
      inputStokVarian.placeholder = "Stok Baru";
      inputStokVarian.required = true;
      inputStokVarian.oninput = function() {
        this.value = this.value.replace(/[^0-9]/g, '');
      };
      inputStokVarian.style.width = "100%"; // Sesuaikan dengan lebar input lainnya

      varianDiv.appendChild(labelVarian);
      varianDiv.appendChild(inputNamaVarian);
      varianDiv.appendChild(labelStok);
      varianDiv.appendChild(inputStokVarian);
      container.appendChild(varianDiv);
    }

    function removeVarian(element) {
      element.parentElement.style.display = 'none';
      element.parentElement.querySelector("input[name='delete_varian[]']").value = '1';
    }
  </script>
</head>

<body>
  <div class="container">
    <h1>Form Edit Produk</h1>
    <form class="edit-form" method="post" action="editOleh_proses.php" enctype="multipart/form-data">
      <input type="hidden" name="id_produk" value="<?= $produk['id_produk']; ?>">

      <!-- Kategori -->
      <div class="form-group-horizontal">
        <label for="kategori">Kategori</label>
        <select id="kategori" name="kategori" required>
          <?php while ($kategori = mysqli_fetch_assoc($result_kategori)) { ?>
            <option value="<?= $kategori['id_kategori']; ?>" <?= ($kategori['id_kategori'] == $produk['id_kategori']) ? 'selected' : ''; ?>>
              <?= $kategori['nama_kategori']; ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <!-- Nama Produk -->
      <div class="form-group-horizontal">
        <label for="nama-produk">Nama Produk</label>
        <input type="text" name="nama" id="nama-produk" value="<?= $produk['nama_produk']; ?>" required>
      </div>

      <!-- Isi Produk -->
      <div class="form-group-horizontal">
        <label for="deskripsi-produk">Isi Produk</label>
        <input type="text" name="isi" id="deskripsi-produk" value="<?= $produk['isi']; ?>" required>
      </div>

      <!-- Harga Produk -->
      <div class="form-group-horizontal">
        <label for="harga-produk">Harga Produk</label>
        <input type="text" name="harga" id="harga-produk" value="<?= $produk['harga']; ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
      </div>

      <!-- Gambar Produk -->
      <div class="form-group-horizontal">
        <label for="gambar-produk">Gambar Produk</label>
        <input type="file" name="gambar" id="gambar-produk" accept="image/*">
      </div>

      <!-- Input Varian Produk (after) -->
      <div class="form-group-vertical">
        <?php foreach ($varian_list as $index => $varian) { ?>
          <div class="form-group-row" style="margin-bottom: 20px;"> <!-- Jarak antar varian -->

          <input type="hidden" name="id_produk_varian[]" value="<?= $varian['id_produk_varian']; ?>">
          <input type="hidden" name="old_id_varian[]" value="<?= $varian['id_varian']; ?>">
          <input type="hidden" name="delete_varian[]" value="0"> <!-- Hidden field untuk penanda hapus -->

            <label for="varian-produk" style="margin-left: 0px; width: 150px;">Varian Produk</label>
            <input type="text" id="varian-produk" name="nama_varian[]" value="<?= $varian['nama_varian']; ?>" style="width: 100%;">

            <label for="stok-produk">Stok Produk</label>
            <input type="text" name="stok[]" placeholder="Masukkan stok" value="<?= $varian['stok']; ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%;">

            <button type="button" class="check-btn" onclick="removeVarian(this)">X</button>
          </div>
        <?php } ?>

        <!-- Tambah Varian Baru -->
        <div id="newVarianContainer"></div>
        <button type="button" class="btn-simpan" onclick="addVarianInput()" class="btn-tambah-varian" style="margin-top: 0px;">Tambah Varian</button>
        <br>
      </div>

      <!-- Tombol Simpan dan Kembali -->
      <center>
        <div class="form-buttons" style="margin-top: 20px;">
          <button type="button" class="btn-simpan" style="margin-right: 10px; width: 49%;" onclick="window.location.href='halaman_admin.php';">Kembali</button>
          <button type="submit" class="btn-simpan" style="width: 49%;">Simpan</button>
        </div>
      </center>
    </form>
  </div>
</body>

</html>