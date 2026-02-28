<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

// Get settings
$nama_toko = $pengaturan->get('nama_toko') ?? APP_NAME;
$alamat_toko = $pengaturan->get('alamat_toko') ?? '';
$telepon_toko = $pengaturan->get('telepon_toko') ?? '';
$email_toko = $pengaturan->get('email_toko') ?? '';
$nomor_rekening = $pengaturan->get('nomor_rekening') ?? '';
$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);
$footer_struk = $pengaturan->get('footer_struk') ?? 'Terima kasih atas kunjungan Anda!';

$penjualan_id = sanitizeInput($_GET['id']);
$penjualan->id = $penjualan_id;

if (!$penjualan->readOne()) {
    header('Location: penjualan.php');
    exit();
}

// Get customer data if exists
$customer_name = '';
if (!empty($penjualan->customer_id)) {
    $customer->id = $penjualan->customer_id;
    if ($customer->readOne()) {
        $customer_name = $customer->nama_customer;
    }
}

$detail_stmt = $penjualan->getDetailPenjualan($penjualan_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - <?php echo APP_NAME; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');
        
        body {
            font-family: 'Courier New', 'JetBrains Mono', monospace;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .receipt {
            width: 300px;
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 25px 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #000;
        }
        
        .logo-container {
            margin-bottom: 10px;
        }
        
        .logo-container img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .header h1 {
            font-size: 16px;
            margin: 8px 0 5px 0;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 9px;
            line-height: 1.4;
        }
        
        .transaction-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .info-row strong {
            font-weight: 600;
        }
        
        .items {
            margin-bottom: 15px;
        }
        
        .item-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1.5fr;
            gap: 5px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1.5fr;
            gap: 5px;
            margin-bottom: 4px;
            font-size: 10px;
            padding: 3px 0;
        }
        
        .item-row span:first-child {
            font-weight: 500;
        }
        
        .item-row span:last-child {
            text-align: right;
            font-weight: 600;
        }
        
        .summary {
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #000;
            font-size: 9px;
            line-height: 1.5;
        }
        
        .qr-code-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        
        .qr-code-section img {
            max-width: 120px;
            margin: 8px auto;
            display: block;
        }
        
        .qr-code-section p {
            font-size: 8px;
            margin: 5px 0;
            color: #666;
        }
        
        .metode-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #000;
            color: white;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                border: none;
                width: 100%;
                max-width: 300px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .item-diskon {
            color: #e74c3c;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Cetak Struk</button>
        <a href="penjualan.php" class="btn">Transaksi Baru</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>

    <div class="receipt">
        <div class="header">
            <div class="logo-container">
                <img src="assets/roti.png" alt="Logo Toko" onerror="this.style.display='none'">
            </div>
            <h1><?php echo htmlspecialchars($nama_toko); ?></h1>
            <p><?php if ($alamat_toko): ?><?php echo htmlspecialchars($alamat_toko); ?><br><?php endif; ?>
            <?php if ($telepon_toko): ?>Telp: <?php echo htmlspecialchars($telepon_toko); ?><br><?php endif; ?>
            <?php if ($email_toko): ?>Email: <?php echo htmlspecialchars($email_toko); ?><?php endif; ?></p>
        </div>
        
        <div class="transaction-info">
            <div class="info-row">
                <span><strong>No. Transaksi:</strong></span>
                <span><strong><?php echo $penjualan->no_transaksi; ?></strong></span>
            </div>
            
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($penjualan->tanggal_penjualan)); ?></span>
            </div>

            <div class="info-row">
                <span>Metode Pembayaran:</span>
                <span class="metode-badge"><?php echo strtoupper($penjualan->metode_transaksi); ?></span>
            </div>
            <?php if (strtolower($penjualan->metode_transaksi) === 'transfer' && !empty($penjualan->nomor_rekening)): ?>
            <div class="info-row">
                <span>Nomor Rekening:</span>
                <span style="font-weight: bold; letter-spacing: 1px;"><?php echo htmlspecialchars($penjualan->nomor_rekening); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($customer_name)): ?>
            <div class="info-row">
                <span>Customer:</span>
                <span><?php echo $customer_name; ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="items">
            <div class="item-header">
                <span>Item</span>
                <span>Qty</span>
                <span>Harga</span>
                <span>Total</span>
            </div>
            
            <?php 
            $subtotal = 0;
            // Reset statement untuk fetch ulang
            $detail_stmt = $penjualan->getDetailPenjualan($penjualan_id);
            while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)): 
                $item_diskon = isset($row['diskon']) ? floatval($row['diskon']) : 0;
                $item_total_before_diskon = $row['harga_satuan'] * $row['jumlah'];
                $item_total_after_diskon = $item_total_before_diskon - $item_diskon;
                $subtotal += max(0, $item_total_after_diskon);
            ?>
            <div class="item-row">
                <span><?php echo htmlspecialchars($row['nama_roti']); ?></span>
                <span><?php echo $row['jumlah']; ?></span>
                <span><?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></span>
                <span>
                    <?php if ($item_diskon > 0): ?>
                        <span style="text-decoration: line-through; color: #999; font-size: 10px;">
                            <?php echo number_format($item_total_before_diskon, 0, ',', '.'); ?>
                        </span><br>
                        <span style="color: #e74c3c; font-weight: bold;">
                            -<?php echo number_format($item_diskon, 0, ',', '.'); ?>
                        </span><br>
                    <?php endif; ?>
                    <?php echo number_format(max(0, $item_total_after_diskon), 0, ',', '.'); ?>
                </span>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
            </div>
            <?php if (!empty($penjualan->diskon) && $penjualan->diskon > 0): ?>
            <div class="summary-row">
                <span>Diskon:</span>
                <span>Rp <?php echo number_format($penjualan->diskon, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>PPN (<?php echo $ppn_persen; ?>%):</span>
                <span>Rp <?php echo number_format($penjualan->ppn ?? 0, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row total">
                <span>TOTAL:</span>
                <span>Rp <?php echo number_format($penjualan->total_harga, 0, ',', '.'); ?></span>
            </div>
            <?php if ($penjualan->metode_transaksi === 'cash'): ?>
            <div class="summary-row">
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($penjualan->total_bayar, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($penjualan->kembalian, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (strtolower($penjualan->metode_transaksi) === 'transfer'): ?>
        <div class="qr-code-section">
            <p><strong>Transfer Bank</strong></p>
            <p style="font-size: 11px; font-weight: bold; margin: 8px 0;">Status Bayar: <span class="metode-badge"><?php echo strtoupper($penjualan->metode_transaksi); ?></span></p>
            <p style="font-size: 11px; font-weight: bold; margin: 8px 0;">Nominal Transfer:</p>
            <p style="font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 5px 0; color: #000;">
                Rp <?php echo number_format($penjualan->total_harga, 0, ',', '.'); ?>
            </p>
            <?php if (!empty($penjualan->nomor_rekening)): ?>
                <p style="font-size: 11px; font-weight: bold; margin: 8px 0;">Nomor Rekening:</p>
                <p style="font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 5px 0; color: #000;">
                    <?php echo htmlspecialchars($penjualan->nomor_rekening); ?>
                </p>
            <?php elseif (!empty($nomor_rekening)): ?>
                <p style="font-size: 11px; font-weight: bold; margin: 8px 0;">Nomor Rekening Toko:</p>
                <p style="font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 5px 0; color: #000;">
                    <?php echo htmlspecialchars($nomor_rekening); ?>
                </p>
            <?php else: ?>
                <p style="font-size: 10px; color: #666;">Silakan hubungi kasir untuk nomor rekening</p>
            <?php endif; ?>
            <p style="font-size: 9px; margin-top: 10px; color: #666;">
                Transfer sesuai dengan nominal di atas<br>
                No. Transaksi: <?php echo $penjualan->no_transaksi; ?>
            </p>
        </div>
        <?php elseif (strtolower($penjualan->metode_transaksi) === 'qris'): ?>
        <div class="qr-code-section">
            <p><strong>Scan untuk pembayaran</strong></p>
            <?php
            // Generate QR code URL (using a simple QR code API)
            $qr_data = $nama_toko . " - " . $penjualan->no_transaksi . " - Total: Rp " . number_format($penjualan->total_harga, 0, ',', '.');
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($qr_data);
            ?>
            <img src="<?php echo $qr_url; ?>" alt="QR Code Pembayaran">
            <p>Metode: <?php echo strtoupper($penjualan->metode_transaksi); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($penjualan->note)): ?>
        <div class="note-section" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000;">
            <div style="font-weight: bold; margin-bottom: 5px; font-size: 10px;">Catatan:</div>
            <div style="font-size: 9px;"><?php echo htmlspecialchars($penjualan->note); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><strong><?php echo htmlspecialchars($footer_struk); ?></strong></p>
            <p style="margin-top: 8px;">Roti yang sudah dibeli tidak dapat dikembalikan</p>
            <p style="margin-top: 5px; font-size: 8px;">Terima kasih atas kunjungan Anda!</p>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
