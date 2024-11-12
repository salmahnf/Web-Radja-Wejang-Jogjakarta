<?php  
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = $_POST['id_produk'];
    $kategori = $_POST['kategori'];
    $nama_produk = $_POST['nama'];
    $isi = $_POST['isi'];
    $harga = $_POST['harga'];

    // Ambil data dari form
    $varianArray = isset($_POST['nama_varian']) ? $_POST['nama_varian'] : [];
    $stokArray = isset($_POST['stok']) ? $_POST['stok'] : [];
    $idVarianArray = isset($_POST['id_produk_varian']) ? $_POST['id_produk_varian'] : [];
    $deleteVarianArray = isset($_POST['delete_varian']) ? $_POST['delete_varian'] : [];
    $newVarianArray = isset($_POST['new_nama_varian']) ? $_POST['new_nama_varian'] : [];
    $newStokArray = isset($_POST['new_stok']) ? $_POST['new_stok'] : [];

    // Proses upload gambar jika ada file yang diunggah
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $gambar = file_get_contents($_FILES['gambar']['tmp_name']);
        $gambar = addslashes($gambar);
        $query = "UPDATE produk SET nama_produk = '$nama_produk', isi = '$isi', harga = '$harga', foto = '$gambar', id_kategori = '$kategori' WHERE id_produk = '$id_produk'";
    } else {
        $query = "UPDATE produk SET nama_produk = '$nama_produk', isi = '$isi', harga = '$harga', id_kategori = '$kategori' WHERE id_produk = '$id_produk'";
    }

    // Jalankan query update produk
    if (!mysqli_query($konek, $query)) {
        echo "Gagal mengupdate produk: " . mysqli_error($konek);
        exit;
    }

    // Update atau hapus varian yang sudah ada
    foreach ($varianArray as $index => $varian) {
        $stok = isset($stokArray[$index]) ? intval($stokArray[$index]) : 0;
        $id_produk_varian = isset($idVarianArray[$index]) ? intval($idVarianArray[$index]) : 0;
        $delete_varian = isset($deleteVarianArray[$index]) ? intval($deleteVarianArray[$index]) : 0;

        if ($delete_varian === 1) {
            // Hapus varian dari produk_varian jika ditandai untuk dihapus
            $query_delete_varian = "DELETE FROM produk_varian WHERE id_produk_varian = '$id_produk_varian'";
            mysqli_query($konek, $query_delete_varian);
        } else {
            // Update stok untuk varian yang sudah ada
            $query_update_varian = "UPDATE produk_varian SET stok = '$stok' WHERE id_produk_varian = '$id_produk_varian'";
            mysqli_query($konek, $query_update_varian);
        }
    }

    // Tambahkan varian baru
    foreach ($newVarianArray as $index => $new_varian) {
        $new_stok = intval($newStokArray[$index]);

        // Cek apakah varian sudah ada di database
        $query_cek_varian = "SELECT id_varian FROM varian WHERE nama_varian = '$new_varian'";
        $result_cek_varian = mysqli_query($konek, $query_cek_varian);

        if ($result_cek_varian && mysqli_num_rows($result_cek_varian) > 0) {
            // Jika varian sudah ada, gunakan id_varian yang ada
            $row = mysqli_fetch_assoc($result_cek_varian);
            $id_varian_baru = $row['id_varian'];
        } else {
            // Jika varian belum ada, tambahkan ke tabel varian
            $query_insert_varian = "INSERT INTO varian (nama_varian) VALUES ('$new_varian')";
            mysqli_query($konek, $query_insert_varian);
            $id_varian_baru = mysqli_insert_id($konek);
        }

        // Tambahkan entri baru ke tabel produk_varian
        $query_produk_varian = "INSERT INTO produk_varian (id_produk, id_varian, stok) VALUES ('$id_produk', '$id_varian_baru', '$new_stok')";
        mysqli_query($konek, $query_produk_varian);
    }

    // Redirect kembali ke halaman admin setelah update berhasil
    header("Location: halaman_admin.php");
    exit();
} else {
    echo "Invalid request method!";
}
?>
