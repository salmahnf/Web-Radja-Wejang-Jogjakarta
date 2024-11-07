<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <!-- source bootstrap. nb: pas ditambahin link ini gatau knp fontnya ada yg keganti terus geser gitu iconnya -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- style notif -->
    <style type="text/css">
        .alert-floating {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            margin: auto;
            width: 30%;
            border: none; 
            padding: 10px;
            text-align: center;
            margin-top: 80px;
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

    <div class="login-container">
        <div class="login-card">
            <h1>Login</h1>

            <!-- notif login gagal, logout, belum login -->
            <div class="alert-floating">
                <?php
                    if (isset($_GET['pesan'])) 
                    {
                        if ($_GET['pesan'] == "gagal") 
                        { 
                         ?>
                            <div class="alert alert-danger" role="alert">
                            <?php echo "Login gagal. Username atau password salah."; ?>
                            </div>
                         <?php
                        } else if ($_GET['pesan'] == "logout") 
                        {
                         ?>
                            <div class="alert alert-danger" role="alert">
                            <?php echo "Anda telah berhasil logout."; ?>
                            </div>
                         <?php
                        } else if ($_GET['pesan'] == "belum_login") 
                        {
                         ?>
                            <div class="alert alert-danger" role="alert">
                            <?php echo "Silakan login terlebih dahulu."; ?>
                            </div>
                         <?php
                        }
                    }
                ?>
            </div>

            <form action="login_proses.php" method="POST">
                <label for="username">Username</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="username" required />
                    <img src="assets/kunci.png" alt="Lock Icon" class="icon-inside">
                </div>
                
    
                <label for="password">Password</label>
                <div class="input-group">
                    <img src="assets/kunci.png" alt="Lock Icon" class="icon-inside">
                    <input type="password" id="password" name="password" placeholder="password123" required />
                    <img src="assets/mata.png" alt="Eye Icon" class="toggle-password" onclick="togglepw()">
                </div>
                <!-- logika tampil password -->
                <script type="text/javascript">
                    function togglepw()
                    {
                        var showp = document.getElementById('password');

                        if(showp.type==='password') {
                            showp.type='text';
                        } else {
                            showp.type='password';
                        }
                    }
                </script>

                <a href="#" id="forgotPasswordLink" class="forgot-password">Lupa password</a>
    
                <button type="submit" class="login-button">Login</button>
    
                <p class="no-account">
                    Tidak punya akun? <a href="#">Hubungi kami.</a>
                </p>
            </form>
        </div>
    </div>

    <footer class="footer-glassy">
        <div class="social-media">
            <a href="https://www.instagram.com/radjawedang_id" target="_blank">
                <img src="assets/ig.png" alt="Instagram">@radjawedang_id
            </a>
            <a href="https://www.instagram.com/radjawedang_official" target="_blank">@radjawedang_official</a>
            <a href="https://www.facebook.com/radjawedangstore" target="_blank">
                <img src="assets/telp.png" alt="Phone"> Radja Wedang Store
            </a>
        </div>
    </footer>

</body>
</html>