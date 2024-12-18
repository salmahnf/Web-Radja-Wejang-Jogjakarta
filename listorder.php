<?php
include 'koneksi.php'; // Menyertakan file koneksi.php

// Mendapatkan kata kunci dari search bar
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Query SQL dengan kondisi pencarian menggunakan LIKE
$sql = "SELECT o.id_orders, o.nama_pembeli, o.mitra, o.tanggal_pembelian, o.status, 
        GROUP_CONCAT(CONCAT(od.nama_produk, ' (', od.varian, ')') SEPARATOR '<br>') AS barang,
        GROUP_CONCAT(od.jumlah SEPARATOR '<br>') AS kuantitas,
        SUM(od.harga * od.jumlah) AS total_harga
        FROM orders o
        JOIN orders_detail od ON o.id_orders = od.id_orders";

// Tambahkan WHERE jika ada pencarian
if (!empty($keyword)) {
    $sql .= " WHERE o.id_orders LIKE ? 
              OR o.nama_pembeli LIKE ? 
              OR o.mitra LIKE ? 
              OR o.status LIKE ?
              OR o.tanggal_pembelian LIKE ? 
              OR od.nama_produk LIKE ? 
              OR od.varian LIKE ?";
}

// Tambahkan GROUP BY setelah pencarian
$sql .= " GROUP BY o.id_orders";

$stmt = $konek->prepare($sql);

// Bind parameter jika ada pencarian
if (!empty($keyword)) {
    $searchTerm = "%$keyword%";
    $stmt->bind_param("sssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List Order - Radja Wedang Jogjakarta</title>
  <link rel="stylesheet" href="stylelist.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .search-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .clear-btn {
      background-color: #f44336;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
      cursor: pointer;
    }
    .clear-btn:hover {
      background-color: #d32f2f;
    }
    .status-btn {
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      color: white !important;
      font-weight: bold;
      cursor: pointer;
    }

        .btn.delete {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    background-color: #f44336;
    color: white;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    display: inline-block;
    }
    .btn.delete:hover {
    background-color: #d32f2f;
    }

    .status-lunas { background-color: #4caf50; }
    .status-selesai { background-color: #2196f3; }
    .status-belum { background-color: #f44336; }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <img src="assets/logo.png" alt="Radja Wedang Logo">
        <h2>RADJA WEDANG<br><span>JOGJAKARTA</span></h2>
      </div>
      <div class="profile">
        <img src="assets/tara.jpg" alt="Admin Profile">
        <p>Admin 1</p>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard_admin.php"><img src="assets/dashboard - icon.png" alt="Dashboard Icon"> Dashboard</a></li>
          <li><a href="halaman_admin.php"><img src="assets/stok produk-icon.png" alt="Stok Produk Icon"> Kelola Produk</a></li>
          <li><a href="kerjasama.php"><img src="assets/kerjasama-icon.png" alt="Kerjasama Icon"> Kerjasama Tour</a></li>
          <li class="active"><a href="#"><img src="assets/list order - icon.png" alt="List Order Icon"> List Order</a></li>
        </ul>
      </nav>
      <a href="logout.php" class="logout">
        <img src="assets/keluar.png" alt="Logout Icon"> Keluar</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <div class="header-left">
          <h1>List Order</h1>
        </div>
        <div class="search-bar">
          <form method="GET" action="" class="search-container">
            <input type="text" name="search" placeholder="Cari ID, Status, Tanggal, dll..." value="<?php echo htmlspecialchars($keyword); ?>" id="search-input">
            <button type="submit" id="search-btn">Cari</button>
            <?php if (!empty($keyword)): ?>
              <button type="button" class="clear-btn" onclick="window.location.href='listorder.php'">X</button>
            <?php endif; ?>
          </form>
        </div>
      </header>
      <section class="order-list">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>ID Pesanan</th>
              <th>Tanggal Pesanan</th>
              <th>Nama Mitra</th>
              <th>Nama Pembeli</th>
              <th>Produk (Varian)</th>
              <th>Jumlah</th>
              <th>Harga</th>
              <th>Status</th>
              <th>Aksi</th>
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
                    echo "<td>" . $row['mitra'] . "</td>";
                    echo "<td>" . $row['nama_pembeli'] . "</td>";
                    echo "<td>" . $row['barang'] . "</td>";
                    echo "<td>" . $row['kuantitas'] . "</td>";
                    echo "<td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                    echo "<td>
                            <button class='status-btn status-" . strtolower($row['status']) . "' 
                                    data-id='" . $row['id_orders'] . "' 
                                    data-status='" . strtolower($row['status']) . "'>
                              " . ucfirst($row['status']) . "
                            </button>
                          </td>";
                    echo "<td>
                            <a href='delete.php?order_id=" . $row['id_orders'] . "' class='btn delete'>Hapus</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>Tidak ada data ditemukan.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <script>
    $(document).ready(function () {
      $(".status-btn").click(function () {
        const button = $(this);
        const orderId = button.data("id");
        const currentStatus = button.data("status");

        // Tentukan status baru
        let newStatus;
        if (currentStatus === "belum") {
          newStatus = "lunas";
        } else if (currentStatus === "lunas") {
          newStatus = "selesai";
        } else {
          newStatus = "belum";
        }

        // Kirim AJAX request untuk memperbarui status
        $.ajax({
          url: "update_status.php",
          method: "POST",
          data: { id_orders: orderId, status: newStatus },
          success: function (response) {
            if (response === "success") {
              // Update status pada tombol
              button.data("status", newStatus);
              button.removeClass("status-lunas status-selesai status-belum")
                    .addClass("status-" + newStatus)
                    .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
            } else {
              alert("Gagal memperbarui status.");
            }
          }
        });
      });
    });
  </script>
</body>
</html>
