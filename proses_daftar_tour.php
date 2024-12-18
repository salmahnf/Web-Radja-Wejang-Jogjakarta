<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : ''; // Enkripsi password
    $ibu = isset($_POST['ibu']) ? trim($_POST['ibu']) : '';

    // Validasi input wajib diisi
    if (empty($email) || empty($username) || empty($password) || empty($ibu)) {
        die("Semua field wajib diisi!");
    }



    try {
        // Query untuk tabel user
        $stmt2 = $konek->prepare("INSERT INTO user (email, username, password, status, nama_ibu, tanggal_tambah) 
                                  VALUES (?, ?, ?, 'mitra', ?, CURDATE())");
        if (!$stmt2) {
            throw new Exception("Error pada prepare statement: " . $konek->error);
        }

        $stmt2->bind_param("ssss", $email, $username, $password, $ibu);

        // Eksekusi query
        if ($stmt2->execute()) {
            // Ambil ID user yang baru saja dibuat
            $id_user = $konek->insert_id;

            // Redirect ke halaman isi_mitra.php dengan ID user
            header("Location: isi_mitra.php?id_user=" . $id_user);
            exit;
        } else {
            throw new Exception("Error pada eksekusi query: " . $stmt2->error);
        }

        // Tutup statement
        $stmt2->close();
    } catch (Exception $e) {
        echo "Terjadi kesalahan: " . $e->getMessage();
    }

    // Tutup koneksi
    $konek->close();
} else {
    die("Akses tidak valid.");
}
?>
