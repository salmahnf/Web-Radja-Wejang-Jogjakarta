<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap data dari form
    $id_user          = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
    $nama_mitra       = isset($_POST['tour_name']) ? trim($_POST['tour_name']) : '';
    $penanggung_jawab = isset($_POST['penanggung_jawab']) ? trim($_POST['penanggung_jawab']) : '';
    $referal          = isset($_POST['referal']) ? trim($_POST['referal']) : '';
    $telepon          = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
    $rekening         = isset($_POST['rekening']) ? trim($_POST['rekening']) : '';
    $alamat           = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $domisili         = isset($_POST['domisili']) ? trim($_POST['domisili']) : '';
    $keaktifan        = 'Aktif';
    $total_penghasilan = 0;
    $komisi           = 0;

    // Validasi data wajib
    if (empty($id_user) || empty($nama_mitra) || empty($penanggung_jawab) || empty($telepon) || empty($rekening) || empty($alamat) || empty($domisili)) {
        die("Semua field wajib diisi!");
    }

    // Proses upload file
    $foto_profil = null;
    if (isset($_FILES['profile_logo']) && $_FILES['profile_logo']['error'] === UPLOAD_ERR_OK) {
        $foto_profil = file_get_contents($_FILES['profile_logo']['tmp_name']); // Baca file dalam bentuk binary
    }

    try {
        // Siapkan query SQL
        $sql = "INSERT INTO mitra 
                (id_user, nama_mitra, penanggungjawab, keaktifan, domisili, total_penghasilan, komisi, kontak, rekening, alamat, referal, foto_profil) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $konek->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error pada prepare statement: " . $konek->error);
        }

        // Bind parameter awal (tanpa BLOB)
        $stmt->bind_param(
            "issssiissssb",
            $id_user,
            $nama_mitra,
            $penanggung_jawab,
            $keaktifan,
            $domisili,
            $total_penghasilan,
            $komisi,
            $telepon,
            $rekening,
            $alamat,
            $referal
        );

        // Kirim data BLOB
        $stmt->send_long_data(11, $foto_profil); // Indeks 11 sesuai dengan posisi foto_profil

        // Eksekusi query
        if ($stmt->execute()) {
            echo "<script>alert('Data mitra berhasil disimpan dengan ID: $id_user'); window.location.href = 'kerjasama.php';</script>";
        } else {
            throw new Exception("Gagal menyimpan data: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');</script>";
    } finally {
        $stmt->close();
        $konek->close();
    }
}
?>
