<?php
include 'koneksi.php'; // Menyertakan file koneksi database

// Mendapatkan keyword dari parameter GET untuk pencarian
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mendapatkan aktivitas terakhir (limit 5, berdasarkan transaksi terbaru) dengan status "lunas" atau "selesai"
$sqlAktivitas = "SELECT 
                    o.mitra, 
                    o.tanggal_pembelian, 
                    SUM(od.harga * od.jumlah) AS total_transaksi, 
                    m.kontak 
                FROM orders o
                JOIN orders_detail od ON o.id_orders = od.id_orders
                LEFT JOIN mitra m ON o.mitra = m.nama_mitra
                WHERE o.status IN ('lunas', 'selesai')
                GROUP BY o.id_orders
                ORDER BY o.tanggal_pembelian DESC
                LIMIT 5";
$resultAktivitas = $konek->query($sqlAktivitas);

// Query untuk daftar mitra dengan total penghasilan berdasarkan status "lunas" atau "selesai"
$sqlMitra = "SELECT 
                m.nama_mitra, 
                m.domisili, 
                m.kontak, 
                m.referal, 
                SUM(CASE WHEN o.status IN ('lunas', 'selesai') THEN od.harga * od.jumlah ELSE 0 END) AS total_penghasilan, 
                ROUND(SUM(CASE WHEN o.status IN ('lunas', 'selesai') THEN od.harga * od.jumlah ELSE 0 END) * 0.2) AS komisi, 
                CASE
                    WHEN MAX(o.tanggal_pembelian) IS NULL THEN 'Tidak Aktif'
                    WHEN DATEDIFF(CURDATE(), MAX(o.tanggal_pembelian)) <= 7 THEN 'Aktif'
                    WHEN DATEDIFF(CURDATE(), MAX(o.tanggal_pembelian)) <= 30 THEN 'Jarang Aktif'
                    ELSE 'Tidak Aktif'
                END AS keaktifan
             FROM mitra m
             LEFT JOIN orders o ON m.nama_mitra = o.mitra
             LEFT JOIN orders_detail od ON o.id_orders = od.id_orders";

// Jika ada keyword, tambahkan WHERE klausa
if (!empty($keyword)) {
    $sqlMitra .= " WHERE m.nama_mitra LIKE ? OR m.domisili LIKE ? OR m.referal LIKE ? OR m.kontak LIKE ?";
    $sqlMitra .= " GROUP BY m.nama_mitra
                   ORDER BY FIELD(keaktifan, 'Aktif', 'Jarang Aktif', 'Tidak Aktif')";
    $stmt = $konek->prepare($sqlMitra);
    $searchTerm = "%$keyword%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $resultMitra = $stmt->get_result();
} else {
    $sqlMitra .= " GROUP BY m.nama_mitra
                   ORDER BY FIELD(keaktifan, 'Aktif', 'Jarang Aktif', 'Tidak Aktif')";
    $resultMitra = $konek->query($sqlMitra);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radja Wedang Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="stylelist.css">
    <!-- <link rel="stylesheet" href="stylekerjasama.css"> -->
    <style>
        :root {
            --primary-color: #E56244;
        }
        
        .search-bar {
            width: 300px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-btn, .clear-btn, .add-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .clear-btn {
            background-color: #f44336;
        }
        .recent-activities {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .activity-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            background-color: #fff;
            width: calc(25% - 20px);
            margin-top: 10px;
        }
        .activity-header {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .activity-logo {
            width: 40px;
            height: 40px;
            background-color: #eee;
            border-radius: 50%;
        }
        .price {
            font-weight: bold;
            color: var(--primary-color);
        }
        .whatsapp-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }
        .member-list {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px;
            background-color: #fff;
            padding-top: 0px;
            margin-top: 10px;
        }
        .member-header, .member-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 8px 0;
        }
        .member-header {
            font-weight: bold;
            background-color: #f9f9f9;
            border-radius: 5px;
            height: 50px;
        }
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
          <li class="active"><a href="#"><img src="assets/kerjasama-icon.png" alt="Kerjasama Icon"> Kerjasama Tour</a></li>
          <li ><a href="listorder.php"><img src="assets/list order - icon.png" alt="List Order Icon"> List Order</a></li>
        </ul>
      </nav>
      <a href="logout.php" class="logout">
        <img src="assets/keluar.png" alt="Logout Icon"> Keluar</a>
    </aside>


    <main class="main-content">
    <div class="header" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 20px;">
    <h1 style="margin: 0 20px 0 0;">Kerjasama Tour</h1>
    <div class="header-actions" style="display: flex; gap: 20px; align-items: center;">
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="search" class="search-bar" placeholder="Cari mitra..." value="<?php echo htmlspecialchars($keyword); ?>" style="padding: 0.8rem 1.5rem; border: 1px solid #ddd; border-radius: 8px; width: 250px; font-size: 0.9rem;">
            <button type="submit" class="search-btn" style="padding: 0.8rem 1.5rem; border-radius: 8px; background-color: #E56244; color: white;">Cari</button>
            <?php if (!empty($keyword)): ?>
                <button type="button" class="clear-btn" onclick="window.location.href='kerjasama.php'" style="background-color: #ff4444; padding: 0.8rem 1.5rem; color: white; border-radius: 8px;">X</button>
            <?php endif; ?>
        </form>
        <button class="add-button" onclick="window.location.href='daftar_tour.php'"style="background-color: #E56244; color: white; padding: 0.8rem 1.5rem; border-radius: 8px;">Tambah</button>
    </div>
</div>


        <section>
            <h2>Aktivitas Terakhir</h2>
            <div class="recent-activities">
                <?php if ($resultAktivitas->num_rows > 0): ?>
                    <?php while ($row = $resultAktivitas->fetch_assoc()): ?>
                        <div class="activity-card">
                            <div class="activity-header">
                                <div class="activity-logo"></div>
                                <div>
                                    <h3><?php echo htmlspecialchars($row['mitra']); ?></h3>
                                    <p><?php echo htmlspecialchars($row['tanggal_pembelian']); ?></p>
                                </div>
                            </div>
                            <p class="price">+Rp <?php echo number_format($row['total_transaksi'], 0, ',', '.'); ?></p>
                            <a href="https://wa.me/<?php echo htmlspecialchars($row['kontak']); ?>" class="whatsapp-link">
                                <i class="fab fa-whatsapp"></i> Whatsapp
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak ada aktivitas terbaru.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="member-list-container">
            <h2>List Anggota</h2>
            <div class="member-list">
                <div class="member-header">
                    <div>Nama</div>
                    <div>Keaktifan</div>
                    <div>Penghasilan</div>
                    <div>Komisi (20%)</div>
                    <div>Kode Referal</div>
                    <div>Kontak</div>
                </div>
                <?php if ($resultMitra->num_rows > 0): ?>
                    <?php while ($row = $resultMitra->fetch_assoc()): ?>
                        <div class="member-row">
                            <div>
                                <h3><?php echo htmlspecialchars($row['nama_mitra']); ?></h3>
                                <p><?php echo htmlspecialchars($row['domisili']); ?></p>
                            </div>
                            <div><?php echo htmlspecialchars($row['keaktifan']); ?></div>
                            <div>Rp <?php echo number_format($row['total_penghasilan'], 0, ',', '.'); ?></div>
                            <div>Rp <?php echo number_format($row['komisi'], 0, ',', '.'); ?></div>
                            <div><?php echo htmlspecialchars($row['referal']); ?></div>
                            <div>
                                <a href="https://wa.me/<?php echo htmlspecialchars($row['kontak']); ?>" class="whatsapp-link">
                                    <i class="fab fa-whatsapp"></i> Whatsapp
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak ada data mitra.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    </div>
</body>
</html>
