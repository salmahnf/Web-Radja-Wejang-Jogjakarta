<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = intval($_POST['id_produk']);

    // Ambil semua id_varian yang terkait dengan produk ini sebelum dihapus
    $query_ambil_varian = "SELECT id_varian FROM produk_varian WHERE id_produk = ?";
    $stmt_ambil_varian = mysqli_prepare($konek, $query_ambil_varian);
    mysqli_stmt_bind_param($stmt_ambil_varian, "i", $id_produk);
    mysqli_stmt_execute($stmt_ambil_varian);
    $result_varian = mysqli_stmt_get_result($stmt_ambil_varian);
    $varian_ids = mysqli_fetch_all($result_varian, MYSQLI_ASSOC);

    // Hapus varian terkait dari tabel `produk_varian`
    $query_hapus_varian_produk = "DELETE FROM produk_varian WHERE id_produk = ?";
    $stmt_hapus_varian = mysqli_prepare($konek, $query_hapus_varian_produk);
    mysqli_stmt_bind_param($stmt_hapus_varian, "i", $id_produk);
    mysqli_stmt_execute($stmt_hapus_varian);

    // Hapus produk dari tabel `produk`
    $query_hapus_produk = "DELETE FROM produk WHERE id_produk = ?";
    $stmt_hapus_produk = mysqli_prepare($konek, $query_hapus_produk);
    mysqli_stmt_bind_param($stmt_hapus_produk, "i", $id_produk);

    if (mysqli_stmt_execute($stmt_hapus_produk)) {
        // Periksa apakah setiap varian masih digunakan oleh produk lain
        foreach ($varian_ids as $varian) {
            $id_varian = $varian['id_varian'];
            $query_cek_varian = "SELECT COUNT(*) AS jumlah FROM produk_varian WHERE id_varian = ?";
            $stmt_cek_varian = mysqli_prepare($konek, $query_cek_varian);
            mysqli_stmt_bind_param($stmt_cek_varian, "i", $id_varian);
            mysqli_stmt_execute($stmt_cek_varian);
            $result_cek = mysqli_stmt_get_result($stmt_cek_varian);
            $row = mysqli_fetch_assoc($result_cek);

            // Hanya hapus varian jika tidak digunakan oleh produk lain
            if ($row['jumlah'] == 0) {
                $query_hapus_varian = "DELETE FROM varian WHERE id_varian = ?";
                $stmt_hapus_varian_final = mysqli_prepare($konek, $query_hapus_varian);
                mysqli_stmt_bind_param($stmt_hapus_varian_final, "i", $id_varian);
                mysqli_stmt_execute($stmt_hapus_varian_final);
            }
        }
        header("Location: halaman_admin.php?status=success");
    } else {
        echo "Gagal menghapus produk: " . mysqli_error($konek);
    }
} else {
    echo "Invalid request method!";
}
?>
