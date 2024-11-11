<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap source -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Style untuk alert floating -->
    <style>
        .alert-floating {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1055;
            width: 80%;
            max-width: 400px;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>
<body style="background-image: url('assets/loginBg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <header>
        <a href="#"><img src="assets/logo.png" alt="Radja Wejang Logo" class="logo"></a>
        <div class="login">
            <span>Home</span>
            <img src="assets/homeButton.png" alt="User Icon" class="user-icon">
        </div>
    </header>
    <!-- Notifikasi Alert Floating -->
    <div id="alert-container"></div>
    <div class="login-container">
        <div class="login-card">
            <h1>Login</h1>
            <form action="login_proses.php" method="POST">
                <label for="username">Username</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="username" required>
                    <img src="assets/kunci.png" alt="Lock Icon" class="icon-inside">
                </div>
                <label for="password">Password</label>
                <div class="input-group">
                    <img src="assets/kunci.png" alt="Lock Icon" class="icon-inside">
                    <input type="password" id="password" name="password" placeholder="password123" required>
                    <img src="assets/mata.png" alt="Eye Icon" class="toggle-password" onclick="togglepw()">
                </div>
                <!-- Script untuk toggle password visibility -->
                <script>
                    function togglepw() {
                        const passwordField = document.getElementById('password');
                        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                    }
                </script>
                <a href="#" class="forgot-password">Lupa password</a>
                <button type="submit" class="login-button">Login</button>
                <p class="no-account">Tidak punya akun? <a href="#">Hubungi kami.</a></p>
            </form>
        </div>
    </div>
    <footer class="footer-glassy">
        <div class="social-media">
            <a href="https://www.instagram.com/radjawedang_id" target="_blank"><img src="assets/ig.png" alt="Instagram">@radjawedang_id</a>
            <a href="https://www.instagram.com/radjawedang_official" target="_blank">@radjawedang_official</a>
            <a href="https://www.facebook.com/radjawedangstore" target="_blank"><img src="assets/telp.png" alt="Phone"> Radja Wedang Store</a>
        </div>
    </footer>
    <!-- Script untuk menampilkan alert floating -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const pesan = urlParams.get('pesan');
            // Fungsi untuk membuat alert
            function showAlert(type, message) {
                const alertContainer = document.getElementById('alert-container');
                alertContainer.innerHTML = `
                    <div class="alert alert-${type} alert-floating" role="alert">
                        ${message}
                    </div>
                `;
                // Hapus alert setelah 3 detik
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 3000);
            }
            // Menampilkan pesan berdasarkan URL parameter
            if (pesan === "gagal") {
                showAlert('danger', 'Login gagal. Username atau password salah.');
            } else if (pesan === "logout") {
                showAlert('success', 'Anda telah berhasil logout.');
            } else if (pesan === "belum_login") {
                showAlert('warning', 'Silakan login terlebih dahulu.');
            }
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>