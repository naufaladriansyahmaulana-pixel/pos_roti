<?php
require_once 'config/config.php';
requireLogin();

// Include models
require_once 'models/User.php';
require_once 'models/Roti.php';
require_once 'models/Penjualan.php';
require_once 'models/Pembelian.php';

$database = new Database();
$db = $database->getConnection();

// Get dashboard data based on user role
$role = $_SESSION['user_role'];
$stats = [];

if ($role === 'admin' || $role === 'kasir') {
    // Get penjualan stats
    $penjualan = new Penjualan($db);
    $total_penjualan_hari = $penjualan->getTotalPenjualanHari();
    $total_penjualan_bulan = $penjualan->getTotalPenjualanBulan();
    $total_transaksi_hari = $penjualan->getTotalTransaksiHari();
}

if ($role === 'admin' || $role === 'gudang') {
    // Get roti stats
    $roti = new Roti($db);
    $total_roti = $roti->getTotalroti();
    $roti_expired = $roti->getrotiExpired();
    $stok_minimum = $roti->getStokMinimum();
    
    // Get pembelian stats
    $pembelian = new Pembelian($db);
    $total_pembelian_bulan = $pembelian->getTotalPembelianBulan();
}

// Get recent activities
$recent_penjualan = [];
$recent_pembelian = [];

if ($role === 'admin' || $role === 'kasir') {
    $stmt = $penjualan->readRecent(5);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_penjualan[] = $row;
    }
}

if ($role === 'admin' || $role === 'gudang') {
    $stmt = $pembelian->readRecent(5);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_pembelian[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php 
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Welcome Message -->
                <div class="alert alert-info">
                    Selamat datang, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>! 
                    Anda login sebagai <strong><?php echo ucfirst($_SESSION['user_role']); ?></strong>.
                </div>

                <!-- Dashboard Cards -->
                <div class="dashboard-grid">
                    <?php if ($role === 'admin' || $role === 'kasir'): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                <i>💰</i>
                            </div>
                            <div class="card-title">Penjualan Hari Ini</div>
                        </div>
                        <div class="card-value"><?php echo formatCurrency($total_penjualan_hari ?? 0); ?></div>
                        <div class="card-subtitle">Total pendapatan hari ini</div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon success">
                                <i>📊</i>
                            </div>
                            <div class="card-title">Transaksi Hari Ini</div>
                        </div>
                        <div class="card-value"><?php echo $total_transaksi_hari ?? 0; ?></div>
                        <div class="card-subtitle">Jumlah transaksi hari ini</div>
                    </div>
                    <?php endif; ?>

                    <?php if ($role === 'admin' || $role === 'gudang'): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                <i>🍞</i>
                            </div>
                            <div class="card-title">Total roti</div>
                        </div>
                        <div class="card-value"><?php echo $total_roti ?? 0; ?></div>
                        <div class="card-subtitle">Jumlah jenis roti</div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon danger">
                                <i>⚠️</i>
                            </div>
                            <div class="card-title">Stok Minimum</div>
                        </div>
                        <div class="card-value"><?php echo $stok_minimum ?? 0; ?></div>
                        <div class="card-subtitle">roti dengan stok minimum</div>
                    </div>
                    <?php endif; ?>

                    <?php if ($role === 'admin'): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon info">
                                <i>📈</i>
                            </div>
                            <div class="card-title">Penjualan Bulan Ini</div>
                        </div>
                        <div class="card-value"><?php echo formatCurrency($total_penjualan_bulan ?? 0); ?></div>
                        <div class="card-subtitle">Total pendapatan bulan ini</div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                <i>📦</i>
                            </div>
                            <div class="card-title">Pembelian Bulan Ini</div>
                        </div>
                        <div class="card-value"><?php echo formatCurrency($total_pembelian_bulan ?? 0); ?></div>
                        <div class="card-subtitle">Total pembelian bulan ini</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                    <?php if ($role === 'admin' || $role === 'kasir'): ?>
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Penjualan Terbaru</h3>
                        </div>
                        <div style="padding: 0;">
                            <?php if (empty($recent_penjualan)): ?>
                                <p style="padding: 20px; text-align: center; color: #666;">Tidak ada data penjualan</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>Total</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_penjualan as $row): ?>
                                        <tr>
                                            <td><?php echo $row['no_transaksi']; ?></td>
                                            <td><?php echo formatCurrency($row['total_harga']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_penjualan'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($role === 'admin' || $role === 'gudang'): ?>
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Pembelian Terbaru</h3>
                        </div>
                        <div style="padding: 0;">
                            <?php if (empty($recent_pembelian)): ?>
                                <p style="padding: 20px; text-align: center; color: #666;">Tidak ada data pembelian</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No. Faktur</th>
                                            <th>Total</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_pembelian as $row): ?>
                                        <tr>
                                            <td><?php echo $row['no_faktur']; ?></td>
                                            <td><?php echo formatCurrency($row['total_harga']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pembelian'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
