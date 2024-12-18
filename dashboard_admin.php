<?php
include 'koneksi.php';
// Query untuk mendapatkan data penjualan terakhir
// Query untuk mendapatkan data penjualan terakhir
$sql = "
    SELECT o.id_orders, o.nama_pembeli, o.mitra, o.tanggal_pembelian, m.id_mitra 
    FROM orders o 
    JOIN mitra m ON o.id_mitra = m.id_mitra
    ORDER BY o.tanggal_pembelian DESC
    LIMIT 10
";

// Pastikan Anda menggunakan $konek->query() untuk mengeksekusi query
$result = $konek->query($sql);

// Periksa apakah ada hasil
if ($result->num_rows > 0) {
    $sales = [];
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
    }
} else {
    $sales = [];
}
// Query untuk mendapatkan penjualan 30 hari terakhir dan mengelompokkan berdasarkan status
$last_30_days_sales_by_status_sql = "
    SELECT m.nama_mitra,m.id_mitra,  o.status, COUNT(o.id_orders) AS total_orders, 
           SUM(od.harga * od.jumlah) AS total_sales
    FROM orders o
    JOIN mitra m ON o.id_mitra = m.id_mitra
    JOIN orders_detail od ON o.id_orders = od.id_orders
    WHERE o.tanggal_pembelian >= CURDATE() - INTERVAL 30 DAY
    GROUP BY m.id_mitra, o.status
    ORDER BY total_sales DESC
    LIMIT 10
";

$last_30_days_sales_by_status_result = $konek->query($last_30_days_sales_by_status_sql);

if (!$last_30_days_sales_by_status_result) {
    die("Query gagal dijalankan: " . $konek->error);
}

// Query untuk menghitung jumlah mitra total
$total_mitra_sql = "
    SELECT COUNT(*) AS total_mitra 
    FROM user 
    WHERE status = 'mitra'
";

$total_mitra = 0; // Default nilai jika query gagal

// Eksekusi query
if ($total_mitra_result = $konek->query($total_mitra_sql)) {
    if ($total_mitra_row = $total_mitra_result->fetch_assoc()) {
        $total_mitra = isset($total_mitra_row['total_mitra']) ? (int)$total_mitra_row['total_mitra'] : 0;
    }
    // Bebaskan hasil query dari memori
    $total_mitra_result->free();
} else {
    // Jika query gagal, tampilkan error untuk debugging (bisa dihapus di production)
    echo "Error pada query jumlah mitra: " . htmlspecialchars($konek->error);
}

// Query untuk menghitung jumlah mitra yang ditambahkan hari ini
$today_mitra_sql = "
    SELECT COUNT(*) AS mitra_hari_ini
    FROM user
    WHERE status = 'mitra' AND DATE(tanggal_tambah) = CURDATE()
";

$mitra_hari_ini = 0; // Default nilai jika query gagal

// Eksekusi query
if ($today_mitra_result = $konek->query($today_mitra_sql)) {
    if ($today_mitra_row = $today_mitra_result->fetch_assoc()) {
        $mitra_hari_ini = isset($today_mitra_row['mitra_hari_ini']) ? (int)$today_mitra_row['mitra_hari_ini'] : 0;
    }
    // Bebaskan hasil query dari memori
    $today_mitra_result->free();
} else {
    // Jika query gagal, tampilkan error untuk debugging (hapus di production)
    echo "Error pada query mitra hari ini: " . htmlspecialchars($konek->error);
}


// Ambil bulan dari input search atau gunakan bulan saat ini secara default
$search_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Query untuk total penghasilan sesuai bulan yang dicari
$total_income_sql = "
    SELECT SUM(od.harga * od.jumlah) AS total_penghasilan 
    FROM orders_detail od
    JOIN orders o ON od.id_orders = o.id_orders
    WHERE DATE_FORMAT(o.tanggal_pembelian, '%Y-%m') = ?
    AND o.status = 'selesai'
";

$stmt = $konek->prepare($total_income_sql);
$stmt->bind_param('s', $search_month);  // Bind parameter bulan
$stmt->execute();
$total_income_result = $stmt->get_result();
$total_income_row = $total_income_result->fetch_assoc();
$total_penghasilan = isset($total_income_row['total_penghasilan']) ? $total_income_row['total_penghasilan'] : 0;



// Query untuk total penghasilan hari ini
$today_income_sql = "
    SELECT SUM(od.harga * od.jumlah) AS total_hari_ini
    FROM orders_detail od
    JOIN orders o ON od.id_orders = o.id_orders
    WHERE DATE(o.tanggal_pembelian) = CURDATE()
";
$today_income_result = $konek->query($today_income_sql);
$today_income_row = $today_income_result->fetch_assoc();
$total_hari_ini = isset($today_income_row['total_hari_ini']) ? $today_income_row['total_hari_ini'] : 0;

// Query untuk total penghasilan kemarin
$yesterday_income_sql = "
    SELECT SUM(od.harga * od.jumlah) AS total_hari_kemarin
    FROM orders_detail od
    JOIN orders o ON od.id_orders = o.id_orders
    WHERE DATE(o.tanggal_pembelian) = CURDATE() - INTERVAL 1 DAY
";
$yesterday_income_result = $konek->query($yesterday_income_sql);
$total_hari_kemarin = isset($yesterday_income_result->fetch_assoc()['total_hari_kemarin']) 
    ? $yesterday_income_result->fetch_assoc()['total_hari_kemarin'] 
    : 0;

// Hitung persentase peningkatan/pengurangan dari kemarin ke hari ini
$income_percentage = $total_hari_kemarin > 0 ? (($total_hari_ini - $total_hari_kemarin) / $total_hari_kemarin) * 100 : 0;


// Fungsi untuk menghitung waktu yang berlalu dari tanggal pembelian
function time_elapsed_from_date($date) {
    $now = new DateTime(); // Waktu saat ini
    $past = new DateTime($date); // Tanggal pembelian
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ' tahun lalu';
    } elseif ($diff->m > 0) {
        return $diff->m . ' bulan lalu';
    } elseif ($diff->d > 0) {
        return $diff->d . ' hari lalu';
    } else {
        return 'Hari ini';
    }
}

// Query untuk mendapatkan data stok produk di bawah 20
$stock_sql = "
    SELECT p.nama_produk, pv.stok 
    FROM produk_varian pv
    JOIN produk p ON pv.id_produk = p.id_produk
    WHERE pv.stok < 20
";
$stock_result = $konek->query($stock_sql);
$low_stock_products = [];
while ($row = $stock_result->fetch_assoc()) {
    $low_stock_products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List Order - Radja Wedang Jogjakarta</title>
  <link rel="stylesheet" href="stylelist.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
          <li class="active"><a href="#"><img src="assets/dashboard - icon.png" alt="Dashboard Icon"> Dashboard</a></li>
          <li><a href="halaman_admin.php"><img src="assets/stok produk-icon.png" alt="Stok Produk Icon"> Kelola Produk</a></li>
          <li><a href="kerjasama.php"><img src="assets/kerjasama-icon.png" alt="Kerjasama Icon"> Kerjasama Tour</a></li>
          <li><a href="listorder.php"> <img src="assets/list order - icon.png" alt="List Order Icon"> List Order</a></li>
        </ul>
      </nav>
      <a href="logout.php" class="logout">
        <img src="assets/keluar.png" alt="Logout Icon"> Keluar</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content" style="background-color: #ffff;">
      <header>
        <div class="header-left">
          <h1>Dashboard</h1>
          <p><?php echo date('d F Y'); ?></p>
        </div>
        <div class="search-bar">
        <form method="get" style="display: flex; align-items: center;">
          <input type="month" name="month" value="<?php echo htmlspecialchars($search_month); ?>" style="border: 1px solid #F9D6CE; margin-right: 10px;">
          <button type="submit" id="search-btn" style="display: flex; align-items: center;">
            <img src="assets/search-alt-2.png" alt="Cari" style="margin-right: 5px;">
            Cari
          </button>
        </form>
      </div>

      </header>
      
      <div class="content-grid">
        <!-- Kolom Kiri -->
        <section class="left-section">
            <div class="overview-grid">
                <div class="left-section">
                <div class="card small">
                  <h3>Total Penghasilan</h3>
                  <p class="value">Rp <?php echo number_format($total_penghasilan, 0, ',', '.'); ?> <span class="box"><?php echo round($income_percentage, 2); ?>%</span></p>
                </div>
                <div class="card small">
                    <h3>Anggota Kerjasama</h3>
                    <p class="value">
                        <?php echo htmlspecialchars($total_mitra); ?> 
                        <span class="box">+<?php echo htmlspecialchars($mitra_hari_ini); ?></span>
                    </p>
                </div>

                </div>
                <div class="card progress">
                  <h3 style="margin-bottom: 20px; font-weight: bold; text-align: center;">Produk Stok Rendah</h3>
                  <ul style="padding: 0; margin: 0; list-style: none; display: flex; flex-direction: column; gap: 10px;">
                      <?php if (!empty($low_stock_products)): ?>
                          <?php foreach ($low_stock_products as $product): ?>
                              <li style="display: flex; justify-content: space-between; align-items: center; gap: 10px; width: 100%;">
                                  <span style="flex: 1; min-width: 200px; text-align: left;"><?= htmlspecialchars($product['nama_produk']) ?></span>
                                  <span style="font-weight: bold; color: #b12a2a; text-align: center;"><?= htmlspecialchars($product['stok']) ?> unit</span>
                              </li>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <li style="text-align: center;">Tidak ada produk dengan stok rendah.</li>
                      <?php endif; ?>
                  </ul>
                </div>

              </div>
              <section class="latest-orders">
    <h2>Pesanan Terbaru</h2>

    <!-- Menampilkan berdasarkan status -->
    <?php 
    // Kelompokkan hasil berdasarkan status
    $orders_by_status = [
        'selesai' => [],
        'lunas' => [],
        'belum bayar' => [],
        'dibatalkan' => []
    ];
    
    foreach ($last_30_days_sales_by_status_result as $sale) {
        $orders_by_status[$sale['status']][] = $sale;
    }
    ?>
    <!-- Pesanan selesai -->
    <div class="order success">
    <?php if (!empty($orders_by_status['selesai'])): ?>
        <?php foreach ($orders_by_status['selesai'] as $order): ?>
            <div class="order-item" style="display: flex; align-items: center; margin-bottom: 10px;">
                <div class="order-content" style="display: flex; align-items: center; flex-grow: 1;">
                    <img src="get_logo.php?id_mitra=<?= htmlspecialchars($sale['id_mitra']) ?>" alt="<?= htmlspecialchars($sale['mitra']) ?> Logo" style="max-width: 50px; margin-right: 10px;">
                    <div class="text">
                        <p><?= htmlspecialchars($order['nama_mitra']) ?></p><small><?= $order['total_orders'] ?> Orders</small></p>
                    </div>
                </div>
                <div class="order-price" style="text-align: right; margin-left: 360px; flex-shrink: 0;">
                    <strong>Rp <?= number_format($order['total_sales'], 0, ',', '.') ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada pesanan lunas dalam 30 hari terakhir.</p>
    <?php endif; ?>
</div>

<div class="order lunas">
    <?php if (!empty($orders_by_status['lunas'])): ?>
        <?php foreach ($orders_by_status['lunas'] as $order): ?>
            <div class="order-item" style="display: flex; align-items: center; margin-bottom: 10px;">
                <div class="order-content" style="display: flex; align-items: center; flex-grow: 1;">
                    <img src="get_logo.php?id_mitra=<?= htmlspecialchars($sale['id_mitra']) ?>" alt="<?= htmlspecialchars($sale['mitra']) ?> Logo" style="max-width: 50px; margin-right: 10px;">
                    <div class="text">
                        <p><?= htmlspecialchars($order['nama_mitra']) ?></p><small><?= $order['total_orders'] ?> Orders</small></p>
                    </div>
                </div>
                <div class="order-price" style="text-align: right; margin-left: 360px; flex-shrink: 0;">
                    <strong>Rp <?= number_format($order['total_sales'], 0, ',', '.') ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada pesanan lunas dalam 30 hari terakhir.</p>
    <?php endif; ?>
</div>


    <!-- Pesanan Belum Bayar -->
    <div class="order pending">
    <?php if (!empty($orders_by_status['pending'])): ?>
        <?php foreach ($orders_by_status['pending'] as $order): ?>
            <div class="order-item" style="display: flex; align-items: center; margin-bottom: 10px;">
                <div class="order-content" style="display: flex; align-items: center; flex-grow: 1;">
                    <img src="get_logo.php?id_mitra=<?= htmlspecialchars($sale['id_mitra']) ?>" alt="<?= htmlspecialchars($sale['mitra']) ?> Logo" style="max-width: 50px; margin-right: 10px;">
                    <div class="text">
                        <p><?= htmlspecialchars($order['nama_mitra']) ?></p><small><?= $order['total_orders'] ?> Orders</small></p>
                    </div>
                </div>
                <div class="order-price" style="text-align: right; margin-left: 360px; flex-shrink: 0;">
                    <span>Rp <?= number_format($order['total_sales'], 0, ',', '.') ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada pesanan belum dibayar dalam 30 hari terakhir.</p>
    <?php endif; ?>
</div>
    <!-- Pesanan Dibatalkan -->
    <div class="order cancelled">
        
        <?php if (!empty($orders_by_status['dibatalkan'])): ?>
            <?php foreach ($orders_by_status['dibatalkan'] as $order): ?>
              <div class="order-item" style="display: flex; align-items: center; margin-bottom: 10px;">
                <div class="order-content" style="display: flex; align-items: center; flex-grow: 1;">
                    <img src="get_logo.php?id_mitra=<?= htmlspecialchars($sale['id_mitra']) ?>" alt="<?= htmlspecialchars($sale['mitra']) ?> Logo" style="max-width: 50px; margin-right: 10px;">
                    <div class="text">
                        <p><?= htmlspecialchars($order['nama_mitra']) ?></p><small><?= $order['total_orders'] ?> Orders</small></p>
                    </div>
                </div>
                <div class="order-price" style="text-align: right; margin-left: 360px; flex-shrink: 0;">
                    <span>dibatalkan</span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada pesanan dibatalkan dalam 30 hari terakhir.</p>
        <?php endif; ?>
    </div>
</section>

              
          </section>

        <!-- Kolom Kanan -->
        <section class="right-section">
          <div class="recent-sales">
            <h2>Penjualan Terakhir</h2>
            <ul>
              <?php if (!empty($sales)): ?>
                <?php foreach ($sales as $sale): ?>
                  <li>
                    <div class="left">
                      <!-- Gambar Logo Mitra Berdasarkan id_mitra -->
                      <img src="get_logo.php?id_mitra=<?= htmlspecialchars($sale['id_mitra']) ?>" alt="<?= htmlspecialchars($sale['mitra']) ?> Logo">
                      <div class="text">
                        <strong><?= htmlspecialchars($sale['mitra']) ?></strong>
                        <p><?= htmlspecialchars(time_elapsed_from_date($sale['tanggal_pembelian'])) ?></p>
                      </div>
                    </div>
                    <div class="right">
                      <span><?= htmlspecialchars($sale['nama_pembeli']) ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li>
                  <p>Tidak ada penjualan terbaru.</p>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </section>
      </div>
    </main>      
  </div>
</body>
</html>
