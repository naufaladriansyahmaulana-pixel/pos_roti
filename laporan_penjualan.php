<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Penjualan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get laporan data
$stmt = $penjualan->getLaporanPenjualan($start_date, $end_date);

// Calculate summary
$total_penjualan = 0;
$total_diskon = 0;
$total_transaksi = 0;
$laporan_data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_penjualan += $row['total_harga'];
    $total_diskon += $row['diskon'] ?? 0;
    $total_transaksi++;
    $laporan_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php 
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <h1>Laporan Penjualan</h1>
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
                <!-- Filter Section -->
                <div class="form-container">
                    <h3>Filter Laporan</h3>
                    <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="start_date">Tanggal Mulai:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="end_date">Tanggal Selesai:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <button type="button" onclick="window.print()" class="btn btn-secondary">Cetak</button>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                <i>💰</i>
                            </div>
                            <div class="card-title">Total Penjualan</div>
                        </div>
                        <div class="card-value"><?php echo formatCurrency($total_penjualan); ?></div>
                        <div class="card-subtitle">Periode: <?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?></div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon success">
                                <i>📊</i>
                            </div>
                            <div class="card-title">Total Transaksi</div>
                        </div>
                        <div class="card-value"><?php echo $total_transaksi; ?></div>
                        <div class="card-subtitle">Jumlah transaksi dalam periode</div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                <i>📈</i>
                            </div>
                            <div class="card-title">Rata-rata per Transaksi</div>
                        </div>
                        <div class="card-value"><?php echo $total_transaksi > 0 ? formatCurrency($total_penjualan / $total_transaksi) : 'Rp 0'; ?></div>
                        <div class="card-subtitle">Nilai rata-rata per transaksi</div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon danger">
                                <i>🎁</i>
                            </div>
                            <div class="card-title">Total Diskon</div>
                        </div>
                        <div class="card-value"><?php echo formatCurrency($total_diskon); ?></div>
                        <div class="card-subtitle">Total diskon diberikan</div>
                    </div>
                </div>

                <!-- Laporan Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Detail Laporan Penjualan</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Kasir</th>
                                <th>Diskon</th>
                                <th>Total Harga</th>
                                <th>Total Bayar</th>
                                <th>Kembalian</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan_data)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                                        Tidak ada data penjualan untuk periode yang dipilih
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($laporan_data as $row): ?>
                                <tr>
                                    <td><?php echo $row['no_transaksi']; ?></td>
                                    <td><?php echo $row['kasir']; ?></td>
                                    <td><?php echo formatCurrency($row['diskon'] ?? 0); ?></td>
                                    <td><?php echo formatCurrency($row['total_harga']); ?></td>
                                    <td><?php echo formatCurrency($row['total_bayar']); ?></td>
                                    <td><?php echo formatCurrency($row['kembalian']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_penjualan'])); ?></td>
                                    <td>
                                        <a href="struk.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-info btn-sm" target="_blank">Struk</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <style>
        @media print {
            .sidebar, .top-nav, .form-container, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .content {
                padding: 0 !important;
            }
            
            .table-container {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }
            
            .dashboard-grid {
                display: none !important;
            }
        }
    </style>
</body>
</html>
