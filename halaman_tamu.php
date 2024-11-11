<?php
include 'koneksi.php';  // Pastikan file koneksi database sudah benar
session_start();  // Memulai session untuk menggunakan variabel session seperti keranjang

// Proses penambahan produk ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $product_id = $_POST['product_id'];
    $variant = $_POST['variant'];
    $price = $_POST['price'];

    // Inisialisasi keranjang jika belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Cek apakah produk sudah ada dalam keranjang
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id && $item['variant'] == $variant) {
            $item['quantity'] += $quantity;  // Update quantity jika produk sudah ada
            $found = true;
            break;
        }
    }

    // Jika produk belum ada dalam keranjang, tambahkan
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_name' => $product_name,
            'quantity' => $quantity,
            'product_id' => $product_id,
            'variant' => $variant,
            'price' => $price
        ];
    }

    // Set pesan dan status untuk notifikasi
    $_SESSION['cart_message'] = 'Produk telah ditambahkan ke keranjang!';
    $_SESSION['cart_status'] = 'success';

    // Redirect ke halaman yang sama
    header('Location: halaman_tamu.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title id="page-title">Rahmat Tour - Aneka Bakpia</title>
    <link rel="stylesheet" href="styles2.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
</head>
<body style="background-image: url('assets/checkoutBg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container">
        <header class="main-header">
            <div class="logo-container">
                <a href="#"><img src="assets/logo.png" alt="Radja Wejang Logo" class="logo" /></a>
                <button class="keranjang-button" onclick="window.location.href='keranjang.php'">
                    Keranjang
                    <img src="assets/cart1.png" alt="Cart Icon" class="cart-icon" />
                </button>
            </div>
            <div class="user-info">
                <a href="index.html" class="dashboard-link">Dashboard</a>
                <span>Rahmat Tour</span>
                <img src="assets/userLogo.png" alt="User Icon" class="user-icon" />
            </div>
        </header>

        <!-- Section Aneka Bakpia -->
        <div class="section">
            <h2 class="section-title" onclick="toggleSection('bakpia')">
                Aneka Bakpia <span class="toggle-icon" id="icon-bakpia">-</span>
            </h2>
            <div class="product-list" id="bakpia">
                <?php
                // Ambil produk dan varian dari database
                $sql = "SELECT produk.id_produk, produk.foto, produk.nama_produk, produk.isi, produk.harga, varian.varian, produk_varian.stok 
                        FROM produk 
                        JOIN produk_varian ON produk.id_produk = produk_varian.id_produk 
                        JOIN varian ON produk_varian.id_varian = varian.id_varian
                        WHERE produk.id_kategori = '1'";
                $result = $konek->query($sql);

                // Kelompokkan hasil produk berdasarkan id_produk
                $bakpia_products = [];
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $product_id = $row['id_produk'];
                        if (!isset($bakpia_products[$product_id])) {
                            $bakpia_products[$product_id] = [
                                'nama_produk' => $row['nama_produk'],
                                'foto'=> $row['foto'],
                                'isi' => $row['isi'],
                                'harga' => $row['harga'],
                                'stok' => $row['stok'],
                                'variants' => []
                            ];
                        }
                        $bakpia_products[$product_id]['variants'][] = $row['varian'];
                    }
                }

                // Tampilkan produk dengan opsi varian
                foreach ($bakpia_products as $product_id => $product) {
                    echo "<div class='product-card'>
                            <div class='product-img'>
                                <img src='data:image/jpeg;base64," . base64_encode($product['foto']) . "' alt='Product Image' />
                            </div>
                            <div class='product-details'>
                                <div class='product-name'>{$product['nama_produk']}</div>
                                <div class='product-quantity'>Stok: {$product['stok']}</div>
                                <div class='product-quantity'>Isi: {$product['isi']}</div>
                                <div class='product-price'>Rp. " . number_format($product['harga'], 0, ',', '.') . "</div>
                                <div class='product-options'>
                                    <form method='POST' action='keranjang_proses.php'>
                                        <select name='variant'>";
                    foreach ($product['variants'] as $variant) {
                        echo "<option value='{$variant}'>{$variant}</option>";
                    }
                    echo "          </select>
                                        <input type='number' class='quantity-input' name='quantity' value='1' min='1' />
                                        <input type='hidden' name='product_name' value='{$product['nama_produk']}' />
                                        <input type='hidden' name='product_id' value='{$product_id}' /> 
                                        <input type='hidden' name='price' value='{$product['harga']}' />
                                        <input type='hidden' name='foto' value='" . base64_encode($product['foto']) . "' /> 
                                        <button type='submit'>
                                            Tambah ke Keranjang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>";
                }
                ?>
        </div>

        <!-- Section Aneka Keripik -->
        <div class="section">
            <h2 class="section-title" onclick="toggleSection('keripik')">
                Aneka Keripik <span class="toggle-icon" id="icon-keripik">-</span>
            </h2>
            <div class="product-list" id="keripik">
                <?php
                // Fetch products from the database for Aneka Keripik
                $sql2 = "SELECT produk.id_produk, produk.foto, produk.nama_produk, produk.isi, produk.harga, varian.varian, produk_varian.stok 
                        FROM produk 
                        JOIN produk_varian ON produk.id_produk = produk_varian.id_produk 
                        JOIN varian ON produk_varian.id_varian = varian.id_varian
                        WHERE produk.id_kategori = '2'";
                $result2 = $konek->query($sql2);

                $keripik_products = [];
                if ($result2->num_rows > 0) {
                    while ($row = $result2->fetch_assoc()) {
                        $product_id = $row['id_produk'];
                        if (!isset($keripik_products[$product_id])) {
                            $keripik_products[$product_id] = [
                                'nama_produk' => $row['nama_produk'],
                                'foto' => $row['foto'],
                                'isi' => $row['isi'],
                                'harga' => $row['harga'],
                                'stok' => $row['stok'],
                                'variants' => []
                            ];
                        }
                        // Add variant to the respective product
                        $keripik_products[$product_id]['variants'][] = $row['varian'];
                    }
                }

                // Display the products with dynamic select options for variants
                foreach ($keripik_products as $product_id => $product) {
                    echo "<div class='product-card'>
                            <div class='product-img'>
                                <img src='data:image/jpeg;base64," . base64_encode($product['foto']) . "' alt='Product Image' />
                            </div>
                            <div class='product-details'>
                                <div class='product-name'>{$product['nama_produk']}</div>
                                <div class='product-quantity'>Stok: {$product['stok']}</div>
                                <div class='product-quantity'>Isi: {$product['isi']}</div>
                                <div class='product-price'>Rp. " . number_format($product['harga'], 0, ',', '.') . "</div>
                                <div class='product-options'>
                                    <form method='POST' action='keranjang_proses.php'>
                                        <select name='variant'>";
                    foreach ($product['variants'] as $variant) {
                        echo "<option value='{$variant}'>{$variant}</option>";
                    }
                    echo "          </select>
                                        <input type='number' class='quantity-input' name='quantity' value='1' min='1' />
                                        <input type='hidden' name='product_name' value='{$product['nama_produk']}' />
                                        <input type='hidden' name='product_id' value='{$product_id}' /> 
                                        <input type='hidden' name='price' value='{$product['harga']}' />
                                        <input type='hidden' name='foto' value='" . base64_encode($product['foto']) . "' /> 
                                        <button type='submit'>
                                            Tambah ke Keranjang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>";
                }
                ?>
        </div>

        <!-- Footer -->
        <footer class="footer-glassy">
    <div class="social-media">
      <a href="https://www.instagram.com/radjawedang_id" target="_blank">
        <img src="assets/ig.png" alt="Instagram" />@radjawedang_id
      </a>
      <a href="https://www.instagram.com/radjawedang_official" target="_blank"
        >@radjawedang_official</a
      >
      <a href="https://www.facebook.com/radjawedangstore" target="_blank">
        <img src="assets/telp.png" alt="Phone" /> Radja Wedang Store
      </a>
    </div>
  </footer>
  <script>
    let lastScrollY = window.scrollY;
const navbarElements = document.querySelectorAll('.logo-container, .user-info');

window.addEventListener('scroll', () => {
    if (window.scrollY > lastScrollY) {
        // Saat scroll ke bawah, tambahkan kelas hidden-navbar
        navbarElements.forEach(element => element.classList.add('hidden-navbar'));
    } else {
        // Saat scroll ke atas, hapus kelas hidden-navbar
        navbarElements.forEach(element => element.classList.remove('hidden-navbar'));
    }
    lastScrollY = window.scrollY;
});

  </script>

    </div>

    <script src="script.js"></script>
    <!-- Notifikasi -->
    <?php
    if (isset($_SESSION['cart_message']) && isset($_SESSION['cart_status'])) {
        $message = $_SESSION['cart_message'];
        $status = $_SESSION['cart_status'];

        // Tampilkan notifikasi menggunakan JavaScript
        echo "<script>
                alert('$message');
              </script>";

        // Hapus session setelah notifikasi ditampilkan
        unset($_SESSION['cart_message']);
        unset($_SESSION['cart_status']);
    }
    ?>
</body>
</html>


