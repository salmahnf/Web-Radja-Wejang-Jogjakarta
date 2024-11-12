<?php 
include "koneksi.php"; // Hubungkan dengan file koneksi.php

// Query untuk mengambil semua kategori
$query_kategori = "SELECT * FROM kategori";
$result_kategori = mysqli_query($konek, $query_kategori);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rahmat Tour - Aneka Produk</title>
    <link rel="stylesheet" href="styles2.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script>
        // Fungsi untuk update stok berdasarkan varian yang dipilih
        function updateStok(selectElement, stokId) {
            const stokValue = selectElement.options[selectElement.selectedIndex].getAttribute('data-stok');
            document.getElementById(stokId).value = stokValue;
        }
    </script>
</head>
<body style="background-image: url('assets/checkoutBg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
<div class="container">

    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <a href="#"><img src="assets/logo.png" alt="Radja Wejang Logo" class="logo"></a>
            <div class="button-container">
                <button class="keranjang-button" onclick="window.location.href='history_order.php';">History Order</button>
                <button class="keranjang-button" onclick="window.location.href='addOleh.php';">+ Add Oleh</button>
                <button class="keranjang-button" onclick="window.location.href='addKategori.php';">+ Add Kategori</button>
            </div>
        </div>
        <div class="user-info">
            <a href="dashboard.html" class="dashboard-link">Dashboard</a>
            <span>Rahmat Tour</span>
            <img src="assets/userLogo.png" alt="User Icon" class="user-icon">
        </div>
    </header>

    <!-- Menampilkan Produk Berdasarkan Kategori -->
    <?php while ($kategori = mysqli_fetch_assoc($result_kategori)) { 
        $id_kategori = $kategori['id_kategori'];
        $nama_kategori = $kategori['nama_kategori'];

        // Query untuk mengambil produk berdasarkan kategori
        $query_produk = "SELECT p.id_produk, p.nama_produk, p.isi, p.harga, p.foto 
                         FROM produk p 
                         WHERE p.id_kategori = $id_kategori 
                         ORDER BY p.id_produk";
        $result_produk = mysqli_query($konek, $query_produk);
    ?>

    <!-- Section untuk setiap kategori -->
    <div class="section">
        <h2 class="section-title" onclick="toggleSection('kategori-<?= $id_kategori; ?>')">
            <?= $nama_kategori; ?> <span class="toggle-icon" id="icon-kategori-<?= $id_kategori; ?>">+</span>
        </h2>
        <div class="product-list" id="kategori-<?= $id_kategori; ?>">
            <?php while ($row = mysqli_fetch_assoc($result_produk)) { 
                $id_produk = $row['id_produk'];
                
                // Query untuk mendapatkan varian dan stok berdasarkan produk
                $query_varian = "SELECT pv.stok, v.nama_varian 
                                 FROM produk_varian pv
                                 INNER JOIN varian v ON pv.id_varian = v.id_varian
                                 WHERE pv.id_produk = $id_produk";
                $result_varian = mysqli_query($konek, $query_varian);
                $varian_list = mysqli_fetch_all($result_varian, MYSQLI_ASSOC);
            ?>
                <div class="product-card">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['foto']); ?>" alt="<?= $row['nama_produk']; ?>" class="product-image">
                    <div class="product-content">
                        <div class="product-details">
                            <div class="product-name"><?= $row['nama_produk']; ?></div>
                            <div class="product-quantity">Isi: <?= $row['isi']; ?></div>
                            <div class="product-price">Rp. <?= number_format($row['harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="product-options">
                            <!-- Dropdown untuk memilih varian -->
                            <select onchange="updateStok(this, 'stok-<?= $id_produk; ?>')">
                                <?php foreach ($varian_list as $varian) { ?>
                                    <option data-stok="<?= $varian['stok']; ?>">
                                        <?= $varian['nama_varian']; ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <!-- Kotak teks readonly untuk stok sesuai dengan varian yang dipilih -->
                            <input type="text" id="stok-<?= $id_produk; ?>" class="quantity-input" value="<?= $varian_list[0]['stok']; ?>" readonly>
                            
                            <!-- Tombol edit (sementara menggunakan ikon keranjang) -->
                            <button class="add-to-cart-button" onclick="window.location.href='editOleh.php?id=<?= $id_produk; ?>'">
                                <img src="assets/edit.png" alt="Edit Icon" class="cart-icon">
                            </button>                            
                            <!-- Tombol hapus produk -->
                            <form method="post" action="hapus.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display:inline;">
                                <input type="hidden" name="id_produk" value="<?= $id_produk; ?>">
                                <button type="submit" class="trash-button">
                                    <img src="assets/trash.png" alt="Trash Icon" class="trash-icon">
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Footer -->
    <footer class="footer-glassy">
        <div class="social-media">
            <a href="https://www.instagram.com/radjawedang_id" target="_blank">
                <img src="assets/ig.png" alt="Instagram"> @radjawedang_id
            </a>
            <a href="https://www.instagram.com/radjawedang_official" target="_blank">@radjawedang_official</a>
            <a href="https://www.facebook.com/radjawedangstore" target="_blank">
                <img src="assets/telp.png" alt="Phone"> Radja Wedang Store
            </a>
        </div>
    </footer>

</div>

<script>
// Toggle Section
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById("icon-" + sectionId);

    if (section.style.display === "none" || section.style.display === "") {
        section.style.display = "grid";
        icon.textContent = "-";
    } else {
        section.style.display = "none";
        icon.textContent = "+";
    }
}
</script>

</body>
</html>
