<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout with Header</title>
    <link rel="stylesheet" href="style27.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
<?php

// Ambil ID user dari URL
session_start(); // Mulai session

// Ambil ID user dari URL
$id_user = isset($_GET['id_user']) ? intval($_GET['id_user']) : 0;
if ($id_user <= 0) {
    die("ID user tidak valid!");
}
?>

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

    <form id="userForm" action="proses_isi_mitra.php" method="POST" enctype="multipart/form-data">
        <!-- Main Content Section -->
        <div class="container mt-4">
            <!-- Left Section -->
            <div class="left text-center mb-4">
                <input type="file" accept="image/*" id="profile-logo" name="profile_logo" class="form-control mb-2">
                <input type="text" placeholder="Masukkan nama user" name="tour_name" class="form-control mb-2">
                <input type="hidden" name="id_user" value="<?php echo $id_user; ?>">
            </div>

            <!-- Middle Section -->
            <div class="middle">
                <h3>Profile Tour</h3>
                <div class="mb-3">
                    <label for="penanggung-jawab" class="form-label">Nama Penanggung Jawab</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/org.png" alt="Icon" style="width: 20px;">
                        </span>
                        <input type="text" id="penanggung-jawab" name="penanggung_jawab" class="form-control"
                            placeholder="Nama Penanggung Jawab" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="referal" class="form-label">Referal</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/email.png" alt="Icon" style="width: 20px;">
                        </span>
                        <input type="text" id="referal" name="referal" class="form-control"
                            placeholder="Kode Referal" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telepon" class="form-label">Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/telp.png" alt="Icon" style="width: 20px;">
                        </span>
                        <input type="tel" id="telepon" name="telepon" class="form-control"
                            placeholder="085-xxx-xxx-xxx" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="rekening" class="form-label">Rekening</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/landmark.png" alt="Icon" style="width: 20px;">
                        </span>
                        <input type="text" id="rekening" name="rekening" class="form-control"
                            placeholder="2xxx xxxx xxxx" required>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="right">
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/maps.png" alt="Icon" style="width: 20px;">
                        </span>
                        <textarea id="alamat" name="alamat" class="form-control" placeholder="Masukkan Alamat"
                            rows="3" required></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="domisili" class="form-label">Domisili</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <img src="assets/maps.png" alt="Icon" style="width: 20px;">
                        </span>
                        <textarea id="domisili" name="domisili" class="form-control" placeholder="Domisili"
                            rows="3" required></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-success">Simpan</button>
        </div>
    </form>

    <script>
        document.getElementById("userForm").addEventListener("submit", function(event) {
            const requiredFields = ["penanggung-jawab", "referal", "telepon", "rekening", "alamat", "domisili"];
            let isValid = true;

            requiredFields.forEach(function (field) {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add("is-invalid");
                } else {
                    input.classList.remove("is-invalid");
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert("Mohon lengkapi semua field.");
            }
        });
    </script>
</body>

</html>
