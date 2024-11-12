<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kategori = $_POST['kategori'];
    $nama_produk = $_POST['nama'];
    $isi = $_POST['isi'];
    $harga = $_POST['harga'];
    $varianArray = isset($_POST['varian']) ? $_POST['varian'] : [];
    $stokArray = isset($_POST['stok']) ? $_POST['stok'] : []; // Sesuaikan dengan input di form

    // Menangani upload gambar (tipe LONGBLOB)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        // Baca isi file gambar
        $gambar = file_get_contents($_FILES['gambar']['tmp_name']);
        // Escape karakter biner agar tidak terpotong saat disimpan ke database
        $gambar = addslashes($gambar);
    } else {
        echo "Gagal mengupload gambar!";
        exit;
    }

    // Debugging: Cek ukuran gambar
    echo "Ukuran gambar: " . strlen($gambar) . " bytes<br>";

    // Simpan data ke tabel produk tanpa menggunakan bind_param()
    $query_produk = "INSERT INTO produk (nama_produk, isi, harga, foto, id_kategori) 
                     VALUES ('$nama_produk', '$isi', '$harga', '$gambar', '$kategori')";

    if (!mysqli_query($konek, $query_produk)) {
        echo "Gagal menyimpan produk: " . mysqli_error($konek);
        exit;
    }

    // Ambil id_produk yang baru saja ditambahkan
    $id_produk = mysqli_insert_id($konek);

    // Simpan setiap varian ke tabel varian dan produk_varian
    foreach ($varianArray as $index => $varian) {
        // Ambil stok dari input array stok
        $stok = isset($stokArray[$index]) ? intval($stokArray[$index]) : 0;

        // Cek apakah varian sudah ada di tabel varian
        $query_cek_varian = "SELECT id_varian FROM varian WHERE nama_varian = '$varian'";
        $result_cek_varian = mysqli_query($konek, $query_cek_varian);

        if ($result_cek_varian && mysqli_num_rows($result_cek_varian) > 0) {
            $row = mysqli_fetch_assoc($result_cek_varian);
            $id_varian = $row['id_varian'];
        } else {
            // Jika varian belum ada, tambahkan ke tabel varian
            $query_varian = "INSERT INTO varian (nama_varian) VALUES ('$varian')";
            if (mysqli_query($konek, $query_varian)) {
                $id_varian = mysqli_insert_id($konek);
            } else {
                echo "Gagal menyimpan varian: " . mysqli_error($konek);
                exit;
            }
        }

        // Tambahkan ke tabel produk_varian dengan stok yang benar
        $query_produk_varian = "INSERT INTO produk_varian (id_produk, id_varian, stok) 
                                VALUES ('$id_produk', '$id_varian', '$stok')";

        if (!mysqli_query($konek, $query_produk_varian)) {
            echo "Gagal menyimpan produk_varian: " . mysqli_error($konek);
            exit;
        }
    }

    // Redirect ke halaman_admin.php setelah berhasil menyimpan
    header("Location: halaman_admin.php");
    exit;
} else {
    echo "Invalid request method.";
}
?>
