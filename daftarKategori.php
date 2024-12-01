<?php
include 'koneksi.php'; // Menghubungkan ke database

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

// Fungsi untuk menghapus kategori
if (isset($_GET['delete'])) {
    $id_kategori = intval($_GET['delete']);

    // Hapus kategori dari database
    $query_delete = "DELETE FROM kategori WHERE id_kategori = ?";
    $stmt_delete = mysqli_prepare($konek, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_kategori);
    mysqli_stmt_execute($stmt_delete);

    // Redirect setelah penghapusan
    header("Location: daftarKategori.php");
    exit();
}

// Fungsi untuk mengedit kategori
if (isset($_POST['editKategori'])) {
    $id_kategori = intval($_POST['id_kategori']);
    $nama_kategori = trim($_POST['nama_kategori']);

    // Update kategori di database
    $query_update = "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?";
    $stmt_update = mysqli_prepare($konek, $query_update);
    mysqli_stmt_bind_param($stmt_update, "si", $nama_kategori, $id_kategori);
    mysqli_stmt_execute($stmt_update);

    // Redirect setelah pengeditan
    header("Location: daftarKategori.php");
    exit();
}

// Query untuk mengambil semua kategori
$query = "SELECT * FROM kategori ORDER BY id_kategori";
$result = mysqli_query($konek, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kategori</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="kategori.css">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daftar Kategori</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="kategori.css">
        <style>
            .btn-simpan {
                background-color: #FF7051;
                color: #fff;
                font-size: 16px;
                font-weight: bold;
                padding: 12px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-align: center;
                margin-top: 20px;
            }

            .btn-simpan:hover {
                background-color: #e66045;
            }

            /* Tombol Delete */
            .delete-btn {
                text-decoration: none;
                /* Menghilangkan underline */
                display: inline-flex;
                /* Membuatnya seperti button */
                align-items: center;
                /* Menjaga teks berada di tengah secara vertikal */
                justify-content: center;
                /* Menjaga teks berada di tengah secara horizontal */
                height: 35px;
                padding: 5px 15px;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                cursor: pointer;
                text-transform: capitalize;
                font-weight: bold;
                width: 80px;
            }

            /* Tombol Edit dan Delete (tambah tinggi menjadi 35px) */
            .edit-btn,
            .delete-btn {
                height: 35px;
                padding: 5px 15px;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                cursor: pointer;
                text-transform: capitalize;
                font-weight: bold;
                width: 80px;
                display: inline-flex;
                align-items: center;
                /* Vertikal tengah */
                justify-content: center;
                /* Horizontal tengah */
            }

            .edit-btn {
                background-color: #ABEBC6;
                color: #333;
                font-weight: bold;
            }

            .delete-btn {
                background-color: #cb2808;
                color: white;
                font-weight: bold;
            }

            .edit-btn:hover {
                background-color: #8AD190;
            }

            .delete-btn:hover {
                background-color: #EA9890;
            }
        </style>
    </head>

</head>

<body>
    <div class="header">
        <h1>Daftar Kategori</h1>
        <div class="input-section">
            <input type="text" placeholder="Masukkan Kategori" id="category-input" />
            <button id="add-btn" onclick="addKategori()">add</button>
        </div>
    </div>

    <div class="container">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($kategori = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td id="kategori_<?= $kategori['id_kategori']; ?>" class="kategori-name"><?= $kategori['nama_kategori']; ?></td>
                            <td>
                                <!-- Tombol Edit -->
                                <button class="edit-btn" onclick="editKategori(<?= $kategori['id_kategori']; ?>)">edit</button>
                                <!-- Tombol Hapus -->
                                <a href="daftarKategori.php?delete=<?= $kategori['id_kategori']; ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus kategori ini?')">delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <center>
        <div class="form-buttons" style="margin-top: 20px;">
            <button type="button" class="btn-simpan" style="margin-right: 10px; width: 25%;" onclick="window.location.href='halaman_admin.php';">Kembali</button>
        </div>
    </center>



    <script>
        // Fungsi untuk mengedit kategori
        function editKategori(id_kategori) {
            var kategoriNameElement = document.getElementById('kategori_' + id_kategori);
            var kategoriName = kategoriNameElement.textContent;

            // Membuat input untuk mengedit nama kategori
            var inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.value = kategoriName;

            // Mengganti text kategori dengan input
            kategoriNameElement.innerHTML = '';
            kategoriNameElement.appendChild(inputField);

            // Mengganti tombol "edit" dengan tombol "save"
            var editButton = event.target;
            editButton.textContent = 'save';
            editButton.onclick = function() {
                saveEdit(id_kategori, inputField.value);
            };
        }

        // Fungsi untuk menyimpan perubahan kategori
        function saveEdit(id_kategori, new_name) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'daftarKategori.php';

            // Menambahkan input hidden untuk ID kategori dan nama kategori baru
            var inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id_kategori';
            inputId.value = id_kategori;
            form.appendChild(inputId);

            var inputName = document.createElement('input');
            inputName.type = 'hidden';
            inputName.name = 'nama_kategori';
            inputName.value = new_name;
            form.appendChild(inputName);

            var inputSubmit = document.createElement('input');
            inputSubmit.type = 'hidden';
            inputSubmit.name = 'editKategori';
            inputSubmit.value = true;
            form.appendChild(inputSubmit);

            document.body.appendChild(form);
            form.submit();
        }

        // Fungsi untuk menambah kategori baru
        function addKategori() {
            var categoryInput = document.getElementById('category-input');
            var categoryName = categoryInput.value;

            // Membuat form untuk mengirim data kategori baru
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'daftarKategori.php';

            var inputName = document.createElement('input');
            inputName.type = 'hidden';
            inputName.name = 'nama_kategori';
            inputName.value = categoryName;
            form.appendChild(inputName);

            var inputSubmit = document.createElement('input');
            inputSubmit.type = 'hidden';
            inputSubmit.name = 'addKategori';
            inputSubmit.value = true;
            form.appendChild(inputSubmit);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>