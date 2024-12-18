<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Registrasi</title>
    <link rel="stylesheet" href="style27.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
     <!-- Header Section (Navbar) -->
     <header class="header">
        <div class="header-left">
            <img src="assets/logo.png" alt="Logo" class="logo">
            <h1>RADJA WEDANG JOGJAKARTA</h1>
        </div>
        <div class="header-right">
            <a href="#" class="back-button">
                <img src="assets/keluar.png" alt="Logout Icon"> Keluar
            </a>
        </div>
    </header>

    <!-- Form Section -->
    <form id="tourForm" action="proses_daftar_tour.php" method="POST" enctype="multipart/form-data">



    <!-- Form Section -->
    <form id="registrationForm" action="proses_daftar.php" method="POST" class="p-4">

        <!-- Main Content Section -->
        <div class="container mt-4">
            <div class="row justify-content-center">
                    <h3 class="mb-4">Form Registrasi</h3>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <img src="assets/org.png" alt="Icon" style="width: 20px;">
                            </span>
                            <input type="text" id="username" name="username" class="form-control"
                                placeholder="Masukkan username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <img src="assets/email.png" alt="Icon" style="width: 20px;">
                            </span>
                            <input type="email" id="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <img src="assets/kunci.png" alt="Icon" style="width: 20px;">
                            </span>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Password123" required minlength="8">
                            <button type="button" class="btn btn-light input-group-text" id="togglePassword">
                                <img src="assets/mata.png" alt="Toggle Password" style="width: 20px;" id="passwordIcon">
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ibu" class="form-label">Nama Ibu</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <img src="assets/org.png" alt="Icon" style="width: 20px;">
                            </span>
                            <input type="text" id="ibu" name="ibu" class="form-control"
                                placeholder="Masukkan nama ibu" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">
                Simpan
            </button>
        </div>
    </form>

    <!-- Form Validation & Toggle Password Script -->
    <script>
        // Validasi Password
        document.getElementById("registrationForm").addEventListener("submit", function (event) {
            const password = document.getElementById("password").value;
            if (password.length < 8) {
                event.preventDefault();
                alert("Password harus memiliki minimal 8 karakter.");
                document.getElementById("password").focus();
            }
        });

        // Toggle Password Visibility
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("password");
        const passwordIcon = document.getElementById("passwordIcon");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
            // Ubah ikon
            passwordIcon.src = type === "password" ? "assets/mata.png" : "assets/mata-tertutup.png";
        });
    </script>
</body>

</html>
