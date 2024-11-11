<?php
session_start();  // Start the session to access the cart data

// Retrieve cart data from the session or initialize an empty array
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalPrice = 0;
$cartMessage = '';

// Handle item removal (delete)
if (isset($_GET['delete'])) {
    $indexToDelete = $_GET['delete'];
    if (isset($cart[$indexToDelete])) {
        // Remove the item from the session cart array
        unset($cart[$indexToDelete]);
        $_SESSION['cart'] = array_values($cart);  // Re-index array after removal
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit();
}

// Handle quantity change
if (isset($_GET['update_quantity'])) {
    $indexToUpdate = $_GET['index'];
    $newQuantity = $_GET['quantity'];
    if (isset($cart[$indexToUpdate])) {
        // Update the item quantity in the session
        $cart[$indexToUpdate]['quantity'] = $newQuantity;
        $_SESSION['cart'] = $cart;  // Save updated cart back to session
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit();
}

if (empty($cart)) {
    $cartMessage = "Your cart is empty.";
} else {
    // Calculate the total price of items in the cart
    foreach ($cart as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Radja Wejang Jogjakarta</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body style="background-image: url('assets/checkoutBg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <header class="main-header">
        <a href="#"><img src="assets/logo.png" alt="Radja Wejang Logo" class="logo"></a>
        <div class="login">
            <a href="dashboard.html" class="dashboard-link">Dashboard</a>
            <span>Rahmat Tour</span>
            <img src="assets/userLogo.png" alt="User Icon" class="user-icon">
        </div>
    </header>

    <div class="shopping-cart">
        <div style="display: flex; align-items: center;">
            <a href="halaman_tamu.php" style="margin-right: 10px;">
                <img src="assets/back.png" alt="Back Icon" style="width: 20px; height: auto; cursor: pointer;">
            </a>
            <h2 style="margin-top: 0; font-size: 24px; font-weight: 700; color: #1a1a1a;">Shopping Cart</h2>
        </div>

        <?php if ($cartMessage): ?>
    <p><?= $cartMessage ?></p>
<?php endif; ?>

<?php foreach ($cart as $index => $item): ?>
    <div class="product-item" id="item-<?= $index ?>">
        <input type="checkbox" class="item-checkbox" data-index="<?= $index ?>" onclick="updateTotal()">
        <!-- Di dalam loop foreach untuk menampilkan keranjang -->
        <div class='product-img'>
                    <!-- Tampilan gambar produk dalam base64 -->
                    <img src="data:image/jpeg;base64,<?= base64_encode($item['foto']) ?>" alt="Product Image" style="width: 100px; height: 100px; object-fit: cover;"/>
                </div>
        <div class="product-details">
            <span class="product-name"><?= $item['product_name'] ?></span>
            <select class="product-flavor">
                <option><?= $item['variant'] ?></option>
            </select>
        </div>
        <div class="quantity-selector">
            <button class="quantity-button decrease" data-index="<?= $index ?>" onclick="updateQuantity(<?= $index ?>, 'decrease')">-</button>
            <span class="quantity" id="quantity-<?= $index ?>"><?= $item['quantity'] ?></span>
            <button class="quantity-button increase" data-index="<?= $index ?>" onclick="updateQuantity(<?= $index ?>, 'increase')">+</button>
        </div>
        <span class="product-price" id="price-<?= $index ?>">Rp. <?= number_format($item['price'], 0, ',', '.') ?></span>
        <a href="?delete=<?= $index ?>"><img src="assets/trash.png" alt="Delete" class="delete-icon"></a>
    </div>
<?php endforeach; ?>


        <div class="cart-footer">
            <div class="select-all">
                <input type="checkbox" id="select-all" onclick="toggleSelectAll()">
                <label class="pilih">Pilih Semua</label>
            </div>
            <span class="total-price" id="total-price">Total: Rp. <?= number_format($totalPrice, 0, ',', '.') ?>,-</span>
            <button class="checkout-button">Checkout</button>
        </div>
    </div>

    <script>
        // Update the total price based on selected items
        function updateTotal() {
            let totalPrice = 0;
            const cartItems = document.querySelectorAll('.product-item');
            
            cartItems.forEach((item, index) => {
                const quantity = parseInt(document.getElementById('quantity-' + index).textContent);
                const price = parseInt(item.querySelector('.product-price').textContent.replace('Rp. ', '').replace('.', ''));
                const checkbox = item.querySelector('.item-checkbox');

                if (checkbox.checked) {
                    totalPrice += price * quantity;
                }
            });

            if (totalPrice === 0) {
                document.getElementById('total-price').textContent = 'Total: Rp. 0,-';
            } else {
                document.getElementById('total-price').textContent = 'Total: Rp. ' + totalPrice.toLocaleString() + ',-';
            }
        }

        // Update quantity in session and refresh the page
        function updateQuantity(index, action) {
            let currentQuantity = parseInt(document.getElementById('quantity-' + index).textContent);

            if (action === 'increase') {
                currentQuantity++;
            } else if (action === 'decrease' && currentQuantity > 1) {
                currentQuantity--;
            }

            document.getElementById('quantity-' + index).textContent = currentQuantity;

            // Make a request to update the quantity in session
            window.location.href = "?update_quantity=true&index=" + index + "&quantity=" + currentQuantity;
        }

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');

            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateTotal();
        }

        updateTotal(); // Initial total price update when page loads
    </script>
    
</body>
</html>
