<?php
// Memulai sesi untuk pengguna yang sedang login
session_start();
include 'koneksi.php'; // Pastikan Anda sudah membuat koneksi ke database

// Mendapatkan id_user yang sedang login, misalnya disimpan dalam session
$id_user = $_SESSION['id_user']; // Pastikan Anda sudah set session 'id_user' setelah login

// Ambil data pengguna untuk ditampilkan di form
$query = "SELECT * FROM user INNER JOIN mitra ON user.id_user = mitra.id_user WHERE user.id_user = ?";
$stmt = $konek->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Memproses upload foto profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_profil'])) {
    // Pastikan tidak ada error dalam upload
    if ($_FILES['foto_profil']['error'] == 0) {
        // Baca file gambar yang diupload
        $foto_profil = file_get_contents($_FILES['foto_profil']['tmp_name']); // Mendapatkan isi gambar

        // Query untuk update foto profil
        $update_mitra_query = "UPDATE mitra SET foto_profil = ? WHERE id_user = ?";
        $stmt = $konek->prepare($update_mitra_query);
        $stmt->bind_param("si", $foto_profil, $id_user);

        // Eksekusi query
        if ($stmt->execute()) {
            // Jika sukses, redirect atau beri pesan sukses
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
            exit();
        } else {
            echo "<script>alert('Terjadi kesalahan saat memperbarui foto profil!');</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan saat mengupload foto profil!');</script>";
    }
}

// Jika form di-submit, proses pengeditan data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_FILES['foto_profil'])) {
    // Sanitasi input
    $nama_mitra = isset($_POST['nama_mitra']) ? htmlspecialchars($_POST['nama_mitra']) : htmlspecialchars($_POST['nama_mitra_hidden']);
    $nama_penanggung_jawab = isset($_POST['nama_penanggung_jawab']) ? htmlspecialchars($_POST['nama_penanggung_jawab']) : htmlspecialchars($_POST['nama_penanggung_jawab_hidden']);
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : htmlspecialchars($_POST['username_hidden']);
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($_POST['email_hidden']);
    $telepon = isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : htmlspecialchars($_POST['telepon_hidden']);
    $rekening = isset($_POST['rekening']) ? htmlspecialchars($_POST['rekening']) : htmlspecialchars($_POST['rekening_hidden']);
    $domisili = isset($_POST['domisili']) ? htmlspecialchars($_POST['domisili']) : htmlspecialchars($_POST['domisili_hidden']);
    $alamat = isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : htmlspecialchars($_POST['alamat_hidden']);
    $referal = isset($_POST['referal']) ? $_POST['referal'] :  htmlspecialchars($_POST['referal_hidden']);

    // Cek apakah username sudah digunakan oleh user lain
    $check_username_query = "SELECT * FROM user WHERE username = ? AND id_user != ?";
    $stmt = $konek->prepare($check_username_query);
    $stmt->bind_param("si", $username, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika username sudah digunakan
        echo "<script>alert('Username sudah digunakan, pilih username lain!');</script>";
    } else {
        // Update data di tabel mitra
        $update_mitra_query = "UPDATE mitra 
                               SET nama_mitra = ?, penanggungjawab =?, domisili = ?, kontak = ?, rekening = ?, alamat = ?, referal = ? 
                               WHERE id_user = ?";
        $stmt = $konek->prepare($update_mitra_query);
        $stmt->bind_param("sssssssi", $nama_mitra, $nama_penanggung_jawab, $domisili, $telepon, $rekening, $alamat, $referal, $id_user);

        if ($stmt->execute()) {
            // Update data di tabel user
            $update_user_query = "UPDATE user 
                                  SET username = ?, email = ? 
                                  WHERE id_user = ?";
            $stmt = $konek->prepare($update_user_query);
            $stmt->bind_param("ssi", $username, $email, $id_user);

            if ($stmt->execute()) {
                // Mengalihkan halaman ke halaman yang sama dengan pesan
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
                exit();
            } else {
                echo "<script>alert('Terjadi kesalahan saat memperbarui data pengguna!');</script>";
            }
        } else {
            echo "<script>alert('Terjadi kesalahan saat memperbarui data mitra!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account Settings</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="profile.css" />
    <style>
        .profile-container {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #525252;
            cursor: pointer;
        }

        .edit-icon-container {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: white;
            border-radius: 50%;
            padding: 7px;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            transform: translate(15%, 15%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-icon-container:hover {
            background-color: lightgray;
        }

        .edit-icon {
            font-size: 18px;
        }

        #image-input {
            display: none;
        }

        .edit {
            cursor: pointer;
            margin-left: 10px;
        }

        .form-group input:disabled {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="brand">
                <img src="assets/logoSidebar.png" alt="Logo" class="logo" />
            </div>
            <div class="profile">
                <div class="profile-container">
                    <img
                        id="profile-img"
                        class="profile-image"
                        src="data:image/jpeg;base64,<?php echo base64_encode($user_data['foto_profil']); ?>"
                        alt="Profile Image"
                        onclick="document.getElementById('image-input').click();" />
                    <div
                        class="edit-icon-container"
                        onclick="document.getElementById('image-input').click();">
                        <img src="assets/iedit.png" alt="Edit Icon">
                    </div>
                    <!-- Form upload foto profil -->
                    <form method="POST" enctype="multipart/form-data">
                        <input
                            type="file"
                            name="foto_profil"
                            id="image-input"
                            accept="image/*"
                            onchange="updateProfileImage(event)" style="display:none;" />
                        <button type="submit" style="display:none;" id="upload-btn"></button> <!-- Hidden button untuk submit -->
                    </form>
                </div>

                <h2><?php echo $user_data['nama_mitra']; ?></h2>
                <p><?php echo $user_data['id_user']; ?></p>
            </div>
            <nav class="menu">
                <a href="#" class="menu-item active">
                    <img src="assets/sideprof.png" alt="Profile" class="menu-icon" />
                    Profile
                </a>
                <a href="daftar_pemesanan.php" class="menu-item">
                    <img
                        src="assets/sidepesanan.png"
                        alt="Daftar Pesanan"
                        class="menu-icon" />
                    Daftar Pesanan
                </a>
                <a href="komisi.php" class="menu-item">
                    <img src="assets/sidekomisi.png" alt="Komisi" class="menu-icon" />
                    Komisi
                </a>
            </nav>

            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </aside>
        <main class="content">
            <h1>Account Settings</h1>
            <p class="subtitle">Personalize Your Store and Preferences</p>
            <div class="card">

                <form method="POST" class="form">
                    <input type="hidden" name="id_user" value="<?php echo $user_data['id_user']; ?>" />
                    <div class="form-group">
                        <label>Nama Mitra</label>
                        <div class="input">
                            <img src="assets/iuser.png" alt="User" class="icon" />
                            <input type="text" name="nama_mitra" value="<?php echo $user_data['nama_mitra']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="nama_mitra_hidden" value="<?php echo $user_data['nama_mitra']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Nama Penanggung Jawab</label>
                        <div class="input">
                            <img src="assets/iuser.png" alt="User" class="icon" />
                            <input type="text" name="nama_penanggung_jawab" value="<?php echo $user_data['penanggungjawab']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="nama_penanggung_jawab_hidden" value="<?php echo $user_data['penanggungjawab']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input">
                            <img src="assets/iuser.png" alt="User" class="icon" />
                            <input type="text" name="username" value="<?php echo $user_data['username']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="username_hidden" value="<?php echo $user_data['username']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input">
                            <img src="assets/iemail.png" alt="Email" class="icon" />
                            <input type="email" name="email" value="<?php echo $user_data['email']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="email_hidden" value="<?php echo $user_data['email']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <div class="input">
                            <img src="assets/itelp.png" alt="Phone" class="icon" />
                            <input type="text" name="telepon" value="<?php echo $user_data['kontak']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="telepon_hidden" value="<?php echo $user_data['kontak']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Rekening</label>
                        <div class="input">
                            <img src="assets/irek.png" alt="Rekening" class="icon" />
                            <input type="text" name="rekening" value="<?php echo $user_data['rekening']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="rekening_hidden" value="<?php echo $user_data['rekening']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Domisili</label>
                        <div class="input">
                            <img src="assets/iloc.png" alt="Location" class="icon" />
                            <input type="text" name="domisili" value="<?php echo $user_data['domisili']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="domisili_hidden" value="<?php echo $user_data['domisili']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <div class="input">
                            <img src="assets/iloc.png" alt="Alamat" class="icon" />
                            <input type="text" name="alamat" value="<?php echo $user_data['alamat']; ?>" required disabled />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="alamat_hidden" value="<?php echo $user_data['alamat']; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Kode Referal</label>
                        <div class="input">
                            <img src="assets/iuser.png" alt="Referal" class="icon" />
                            <input
                                type="text"
                                name="referal"
                                value="<?php echo $user_data['referal']; ?>"
                                required
                                disabled
                                maxlength="8"
                                oninput="this.value = this.value.toUpperCase();" />
                            <span class="edit"><i class="fas fa-pencil-alt"></i></span>
                        </div>
                        <input type="hidden" name="referal_hidden" value="<?php echo $user_data['referal']; ?>" />
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Update profile image when changed
        // Update profil image secara langsung saat dipilih
        function updateProfileImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                document.getElementById('profile-img').src = reader.result;
                document.getElementById('upload-btn').click(); // Secara otomatis submit form untuk upload foto
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        document.querySelectorAll('.edit').forEach(function(editBtn) {
            editBtn.addEventListener('click', function() {
                const inputField = this.previousElementSibling; // Input yang ingin diubah
                const hiddenInput = inputField.previousElementSibling; // Hidden input yang menyimpan nilai sebelumnya

                // Jika input dalam kondisi disabled, aktifkan dan update hidden input
                if (inputField.disabled) {
                    inputField.disabled = false; // Aktifkan input
                    inputField.focus(); // Fokuskan input
                    hiddenInput.value = inputField.value; // Update nilai hidden input dengan nilai yang ada
                }
            });
        });


        // Form submission on enter key
        document.querySelector('.form').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Mencegah form dari submit normal
                this.submit(); // Submit form secara manual
            }
        });
    </script>
</body>

</html>