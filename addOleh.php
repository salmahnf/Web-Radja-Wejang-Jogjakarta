<?php 
include "koneksi.php";

$query = "SELECT * FROM kategori";
$hasil = mysqli_query($konek, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tambah Oleh-Oleh</title>
</head>
<body>
    <form method="post" action="addOleh_proses.php" enctype="multipart/form-data">
        <!-- Pilih Kategori -->
        Kategori:
        <select name="kategori">
            <?php while ($data = mysqli_fetch_array($hasil)) { ?>
                <option value="<?= $data['id_kategori'] ?>"><?= $data['nama_kategori'] ?></option>
            <?php } ?>
        </select>
        <br>

        <!-- Input Nama Produk -->
        <label for="nama">Nama produk:</label>
        <input type="text" name="nama" id="nama" required>
        <br>

        <!-- Input Isi Produk -->
        <label for="isi">Isi produk:</label>
        <input type="text" name="isi" id="isi" required>
        <br>

        <!-- Input Harga Produk -->
        <label for="harga">Harga produk:</label>
        <input type="text" name="harga" id="harga" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="\d*">
        <br>

        <!-- Upload Gambar Produk -->
        <label for="gambar">Gambar produk:</label>
        <input type="file" name="gambar" id="gambar" accept="image/*" required>
        <br>

        <!-- Input Varian Produk -->
        <label for="varian">Jumlah Varian:</label>
        <input type="number" id="varian" min="1" />
        <button type="button" onclick="generateVarianInput()">OK</button>
        <div id="varianContainer" class="varian-input"></div>
        <br>

        <!-- Tombol Simpan -->
        <button type="submit">Simpan</button>
    </form>

    <script>
        function generateVarianInput() {
            const varian = document.getElementById('varian').value;
            const varianContainer = document.getElementById('varianContainer');
            varianContainer.innerHTML = ''; // Reset input sebelumnya

            for (let i = 0; i < varian; i++) {
                const varianGroup = document.createElement('div');
                varianGroup.style.display = 'flex';
                varianGroup.style.alignItems = 'center';
                varianGroup.style.marginBottom = '10px';

                // Input untuk nama varian
                const inputVarian = document.createElement('input');
                inputVarian.type = 'text';
                inputVarian.name = 'varian[]';
                inputVarian.placeholder = `Varian ${i + 1}`;
                inputVarian.required = true;
                inputVarian.style.marginRight = '10px';

                // Input untuk stok varian
                const inputStok = document.createElement('input');
                inputStok.type = 'text';
                inputStok.name = 'stok[]';
                inputStok.placeholder = `Stok ${i + 1}`;
                inputStok.required = true;
                inputStok.oninput = function () {
                    this.value = this.value.replace(/[^0-9]/g, ''); // Hanya angka
                };
                inputStok.style.width = '80px';

                // Menambahkan elemen ke varianGroup
                varianGroup.appendChild(inputVarian);
                varianGroup.appendChild(inputStok);
                varianContainer.appendChild(varianGroup);
            }
        }
    </script>
</body>
</html>