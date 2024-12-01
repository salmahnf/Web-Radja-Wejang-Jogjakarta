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
    <title>Form Add Produk</title>
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
            inputNamaVarian.name = "varian[]";
            inputNamaVarian.placeholder = "Masukkan varian produk";
            inputNamaVarian.required = true;
            inputNamaVarian.style.width = "100%"; // Sesuaikan dengan lebar input lainnya

            // Label untuk Stok Varian
            const labelStok = document.createElement("label");
            labelStok.setAttribute("for", "stok-varian");
            labelStok.innerText = "Stok Produk";

            // Input untuk stok varian
            const inputStokVarian = document.createElement("input");
            inputStokVarian.type = "text";
            inputStokVarian.name = "stok[]";
            inputStokVarian.placeholder = "Masukkan stok";
            inputStokVarian.required = true;
            inputStokVarian.oninput = function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            };
            inputStokVarian.style.width = "100%"; // Sesuaikan dengan lebar input lainnya

            // Hidden field for penanda hapus
            const inputDeleteVarian = document.createElement("input");
            inputDeleteVarian.type = "hidden";
            inputDeleteVarian.name = "delete_varian[]";
            inputDeleteVarian.value = "0"; // Default value is 0, meaning not deleted

            // Tombol hapus varian (X)
            const removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add("check-btn");
            removeButton.innerText = "X";
            removeButton.onclick = function() {
                varianDiv.style.display = 'none';
                inputDeleteVarian.value = '1'; // When removed, set value to 1 indicating deletion
                // Hapus elemen varian dari DOM
                setTimeout(() => { 
                    container.removeChild(varianDiv); 
                }, 100);  // Tunggu sejenak sebelum menghapus elemen dari DOM
            };

            varianDiv.appendChild(labelVarian);
            varianDiv.appendChild(inputNamaVarian);
            varianDiv.appendChild(labelStok);
            varianDiv.appendChild(inputStokVarian);
            varianDiv.appendChild(inputDeleteVarian); // Add the hidden input for delete marker
            varianDiv.appendChild(removeButton);
            container.appendChild(varianDiv);
        }

        function removeVarian(element) {
            const varianDiv = element.parentElement;
            const deleteInput = varianDiv.querySelector("input[name='delete_varian[]']");

            // Set value of delete marker to '1'
            deleteInput.value = '1';

            // Remove the entire varian div from the form
            varianDiv.style.display = 'none'; // Hide it visually
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Form Add Produk</h1>
        <form class="edit-form" method="post" action="addOleh_proses.php" enctype="multipart/form-data">

            <!-- Kategori -->
            <div class="form-group-horizontal">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <?php while ($data = mysqli_fetch_array($hasil)) { ?>
                        <option value="<?= $data['id_kategori'] ?>"><?= $data['nama_kategori'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- Nama Produk -->
            <div class="form-group-horizontal">
                <label for="nama-produk">Nama Produk</label>
                <input type="text" name="nama" id="nama-produk" placeholder="Masukkan nama produk" required>
            </div>

            <!-- Isi Produk -->
            <div class="form-group-horizontal">
                <label for="deskripsi-produk">Isi Produk</label>
                <input type="text" id="deskripsi-produk" name="isi" placeholder="Masukkan isi produk">
            </div>

            <!-- Harga Produk -->
            <div class="form-group-horizontal">
                <label for="harga-produk">Harga Produk</label>
                <input type="text" name="harga" id="harga-produk" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="\d*" placeholder="Masukkan harga produk">
            </div>

            <!-- Gambar Produk -->
            <div class="form-group-horizontal">
                <label for="gambar-produk">Gambar Produk</label>
                <input type="file" name="gambar" id="gambar-produk" accept="image/*" required>
            </div>

            <!-- Input Varian Produk -->
            <div class="form-group-vertical">

                <div class="form-group-row" style="margin-bottom: 20px;"> <!-- Jarak antar varian -->
                    <label for="varian-produk" style="margin-left: 0px; width: 150px;">Varian Produk</label>
                    <input type="text" id="varian-produk" name="varian[]" style="width: 100%;" placeholder="Masukkan varian produk" required>

                    <label for="stok-produk">Stok Produk</label>
                    <input type="text" name="stok[]" id="stok-produk" placeholder="Masukkan stok produk" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="width: 100%;" required>

                    <!-- Hidden field for delete marker -->
                    <input type="hidden" name="delete_varian[]" value="0"> <!-- Marking no deletion initially -->

                    <button type="button" class="check-btn" onclick="removeVarian(this)">X</button>
                </div>

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
