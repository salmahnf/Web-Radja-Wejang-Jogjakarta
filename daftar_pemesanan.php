<?php
include 'koneksi.php'; // Menyertakan file koneksi.php
session_start();

// Pastikan mitra sudah login
if (!isset($_SESSION['id_user'])) {
    // Jika tidak ada sesi id_user, redirect ke halaman login
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user']; // ID User yang sedang login

// Query untuk mendapatkan nama mitra berdasarkan id_user
$sql_mitra = "SELECT nama_mitra FROM mitra WHERE id_user = ?";
$stmt_mitra = $konek->prepare($sql_mitra);
$stmt_mitra->bind_param("i", $id_user);
$stmt_mitra->execute();
$result_mitra = $stmt_mitra->get_result();

if ($result_mitra->num_rows > 0) {
    $row_mitra = $result_mitra->fetch_assoc();
    $nama_mitra = $row_mitra['nama_mitra']; // Menyimpan nama mitra
} else {
    // Jika tidak ada mitra yang ditemukan, redirect ke halaman login
    header("Location: login.php");
    exit();
}

$keyword = isset($_GET['search']) ? $_GET['search'] : ''; // Mendapatkan kata kunci pencarian

// Query SQL untuk mendapatkan daftar pesanan berdasarkan mitra yang sedang login
$sql = "SELECT o.id_orders, o.nama_pembeli, o.mitra, o.tanggal_pembelian, o.status, 
        GROUP_CONCAT(CONCAT(od.nama_produk, ' (', od.varian, ')') SEPARATOR '<br>') AS barang,
        GROUP_CONCAT(od.jumlah SEPARATOR '<br>') AS kuantitas,
        SUM(od.harga * od.jumlah) AS total_harga
        FROM orders o
        JOIN orders_detail od ON o.id_orders = od.id_orders
        WHERE o.mitra = ?";

// Menambahkan filter pencarian jika ada
if (!empty($keyword)) {
    $sql .= " AND (o.id_orders LIKE ? 
                OR o.nama_pembeli LIKE ? 
                OR o.mitra LIKE ? 
                OR o.status LIKE ? 
                OR o.tanggal_pembelian LIKE ? 
                OR od.nama_produk LIKE ? 
                OR od.varian LIKE ?)";
}

// Tambahkan GROUP BY setelah pencarian
$sql .= " GROUP BY o.id_orders";

$stmt = $konek->prepare($sql);

// Bind parameter untuk mitra dan pencarian
if (!empty($keyword)) {
    $searchTerm = "%$keyword%";
    $stmt->bind_param("ssssssss", $nama_mitra, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt->bind_param("s", $nama_mitra);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Pesanan</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap"
    rel="stylesheet"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />
  <link rel="stylesheet" href="profile.css" />
  <style>
    /* Container untuk profile */
    .profile-container {
      position: relative;
      width: 100px;
      height: 100px;
    }

    /* Gambar profile berbentuk lingkaran dengan border hitam */
    .profile-image {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #525252; /* Border hitam di sekitar gambar profile */
      cursor: pointer; /* Membuat gambar profil bisa diklik */
    }

    /* Tombol yang mengandung ikon pensil */
    .edit-icon-container {
      position: absolute;
      bottom: 0;
      right: 0;
      background-color: white;
      border-radius: 50%;
      padding: 7px; /* Perbesar area klik */
      cursor: pointer;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
      transform: translate(15%, 15%); /* Menggeser sedikit ke dalam */
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .edit-icon-container:hover {
      background-color: lightgray;
    }

    .edit-icon {
      font-size: 18px; /* Ukuran ikon pensil */
    }

    /* Input file untuk memilih gambar, disembunyikan */
    #image-input {
      display: none;
    }

    .main-content {
      flex: 1;
      padding: 20px;
      background-color: #fad1c8;
    }
    .main-content header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .main-content header .header-left h1 {
      font-size: 24px;
      color: #333;
    }

    .main-content header .header-left p {
      color: #666;
    }

    .main-content .search-bar {
    display: flex;             /* Menggunakan Flexbox agar elemen dalam satu baris */
    align-items: center;       /* Menyelaraskan item secara vertikal */
    gap: 10px;                 /* Memberikan jarak antar elemen */
}

.main-content .search-bar input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px 0 0 5px;
    font-size: 14px;
    width: 250px;              /* Lebar input field */
    height: 40px;              /* Menyamakan tinggi dengan tombol */
}

.main-content .search-bar button {
    padding: 10px 20px;
    background-color: #f37258;
    color: white;
    border: none;
    border-radius: 0 5px 5px 0;
    cursor: pointer;
    font-size: 14px;
    height: 40px;              /* Menyamakan tinggi tombol dengan input */
    /* display: flex;
    align-items: center;
    justify-content: center; */
    gap: 10px;                 /* Jarak antara ikon dan teks dalam tombol */
}


    .order-list {
      margin: 0 auto;
      width: 90%; /* Lebih lebar agar tidak kosong */
    }

    .order-list table {
      width: 100%;
      border-collapse: separate; /* Jarak antar baris */
      border-spacing: 0 15px; /* Memberi jarak antar baris */
      font-family: 'Montserrat', sans-serif; /* Menggunakan font Montserrat */
    }

    .order-list th,
    .order-list td {
      padding: 15px 20px; /* Memberi ruang dalam sel */
      text-align: center;
      font-weight: bold; /* Montserrat bold */
      background-color: #ffffff; /* Warna latar putih */
      border: none;
    }

    .order-list th {
      background-color: #fad1c8; /* Warna header */
      color: #333; /* Warna teks header */
    }

    .order-list tbody tr {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Bayangan untuk baris */
    }

    .order-list tbody tr:hover {
      background-color: #fcefe9; /* Efek hover */
    }

    .order-list tbody tr td {
      background-color: #fff;
      text-align: center; /* Konten tetap di tengah */
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
          <!-- Gambar profil yang bisa diklik -->
          <img
            id="profile-img"
            class="profile-image"
            src="assets/tourLogo.png"
            alt="Profile Image"
            onclick="document.getElementById('image-input').click();"
          />
        </div>
        <h2><?php echo htmlspecialchars($nama_mitra); ?></h2>
        <p><?php echo htmlspecialchars($id_user); ?></p>
      </div>
      <nav class="menu">
        <a href="editProfile.php" class="menu-item">
          <img src="assets/sideprof.png" alt="Profile" class="menu-icon" />
          Profile
        </a>
        <a href="daftar_pemesanan.php" class="menu-item active">
          <img
            src="assets/sidepesanan.png"
            alt="Daftar Pesanan"
            class="menu-icon"
          />
          Daftar Pesanan
        </a>
        <a href="komisi.php" class="menu-item">
          <img src="assets/sidekomisi.png" alt="Komisi" class="menu-icon" />
          Komisi
        </a>
      </nav>

      <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </aside>
    <main class="main-content">
      <header>
        <div class="header-left">
          <h1>Dashboard</h1>
        </div>
        <div class="search-bar">
          <form method="GET" action="" class="search-container" style="width:400px;">
            <input type="text" name="search" placeholder="Cari ID, Status, Tanggal, dll..." value="<?php echo htmlspecialchars($keyword); ?>" id="search-input">
            <button type="submit" id="search-btn">Cari</button>
          </form>
        </div>
      </header>
      <div class="order-list">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pesanan</th>
              <th>Tanggal Pesanan</th>
              <th>Nama Pembeli</th>
              <th>Produk (varian)</th>
              <th>Jumlah</th>
              <th>Harga</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $row['id_orders'] . "</td>";
                    echo "<td>" . $row['tanggal_pembelian'] . "</td>";
                    echo "<td>" . $row['nama_pembeli'] . "</td>";
                    echo "<td>" . $row['barang'] . "</td>";
                    echo "<td>" . $row['kuantitas'] . "</td>";
                    echo "<td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                    echo "<td>" . ucfirst($row['status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Tidak ada data ditemukan.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
