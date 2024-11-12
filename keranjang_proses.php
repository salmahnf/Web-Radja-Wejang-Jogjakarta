<?php
session_start();
include 'koneksi.php';

if (isset($_POST['product_name'], $_POST['quantity'], $_POST['product_id'], $_POST['variant'], $_POST['price'])) {
    $productName = $_POST['product_name'];
    $quantity = (int)$_POST['quantity'];
    $productId = (int)$_POST['product_id'];
    $variant = $_POST['variant'];
    $price = (int)$_POST['price'];

    $stmt = $konek->prepare("SELECT foto FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($foto);
    $stmt->fetch();
    $stmt->close();

    // Jika gambar ditemukan, simpan dalam session
    if ($foto) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cart = &$_SESSION['cart'];
        $productFound = false;

        // Periksa apakah produk dengan varian yang sama sudah ada di keranjang
        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId && $item['variant'] === $variant) {
                // Jika ada, tambahkan jumlahnya
                $item['quantity'] += $quantity;
                $productFound = true;
                break;
            }
        }

        // Jika produk belum ada, tambahkan produk baru ke keranjang
        if (!$productFound) {
            $cart[] = [
                'product_name' => $productName,
                'quantity' => $quantity,
                'product_id' => $productId,
                'variant' => $variant,
                'price' => $price,
                'foto' => $foto // Simpan data gambar BLOB di session
            ];
        }

        $_SESSION['cart_message'] = 'Produk berhasil ditambahkan ke keranjang';
        $_SESSION['cart_status'] = 'success';
    } else {
        $_SESSION['cart_message'] = 'Produk tidak ditemukan';
        $_SESSION['cart_status'] = 'error';
    }
} else {
    $_SESSION['cart_message'] = 'Data produk tidak lengkap';
    $_SESSION['cart_status'] = 'error';
}

// Arahkan kembali ke halaman tamu setelah operasi selesai
header('Location: halaman_tamu.php');
exit;
?>
