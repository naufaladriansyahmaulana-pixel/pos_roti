<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Roti.php';
require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$roti = new Roti($db);
$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

// Get PPN setting
$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);

$message = '';
$message_type = '';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'search_roti':
            $keyword = sanitizeInput($_GET['keyword']);
            $stmt = $roti->search($keyword);
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
            echo json_encode($results);
            exit;
            
        case 'get_roti':
            $roti_id = sanitizeInput($_GET['roti_id']);
            $roti->id = $roti_id;
            if ($roti->readOne()) {
                echo json_encode([
                    'id' => $roti->id,
                    'kode_roti' => $roti->kode_roti,
                    'nama_roti' => $roti->nama_roti,
                    'harga_jual' => $roti->harga_jual,
                    'diskon' => $roti->diskon ?? 0,
                    'stok' => $roti->stok,
                    'gambar_roti' => $roti->gambar_roti
                ]);
            } else {
                echo json_encode(['error' => 'roti tidak ditemukan']);
            }
            exit;
            
    }
}

// Handle transaction submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'process_transaction') {
    try {
        $db->beginTransaction();
        
        // Create penjualan record
        $penjualan->no_transaksi = $penjualan->generateNoTransaksi();
        $penjualan->user_id = $_SESSION['user_id'];
        $penjualan->customer_id = !empty($_POST['customer_id']) ? sanitizeInput($_POST['customer_id']) : null;
        $penjualan->diskon = sanitizeInput($_POST['diskon']);
        $penjualan->ppn = sanitizeInput($_POST['ppn']);
        $penjualan->total_harga = sanitizeInput($_POST['total_harga']);
        $penjualan->total_bayar = sanitizeInput($_POST['total_bayar']);
        $penjualan->kembalian = sanitizeInput($_POST['kembalian']);
        $penjualan->metode_transaksi = !empty($_POST['metode']) ? sanitizeInput($_POST['metode']) : 'cash';
        $penjualan->nomor_rekening = !empty($_POST['nomor_rekening']) ? sanitizeInput($_POST['nomor_rekening']) : null;
        $penjualan->note = !empty($_POST['note']) ? sanitizeInput($_POST['note']) : null;
        
        if (!$penjualan->create()) {
            throw new Exception('Gagal membuat transaksi');
        }
        
        $penjualan_id = $db->lastInsertId();
        
        // Process detail penjualan
        $items = json_decode($_POST['items'], true);
        foreach ($items as $item) {
            // Insert detail penjualan
            $detail_query = "INSERT INTO detail_penjualan (penjualan_id, roti_id, jumlah, harga_satuan, diskon, subtotal) 
                             VALUES (:penjualan_id, :roti_id, :jumlah, :harga_satuan, :diskon, :subtotal)";
            $detail_stmt = $db->prepare($detail_query);
            $detail_stmt->bindParam(':penjualan_id', $penjualan_id);
            $detail_stmt->bindParam(':roti_id', $item['id']);
            $detail_stmt->bindParam(':jumlah', $item['quantity']);
            $detail_stmt->bindParam(':harga_satuan', $item['price']);
            $item_diskon = isset($item['diskon']) ? $item['diskon'] : 0;
            $detail_stmt->bindParam(':diskon', $item_diskon);
            $detail_stmt->bindParam(':subtotal', $item['subtotal']);
            
            if (!$detail_stmt->execute()) {
                throw new Exception('Gagal menyimpan detail penjualan');
            }
            
            // Update stok roti
            $roti->updateStok($item['id'], -$item['quantity']);
        }
        
        $db->commit();
        
        // Redirect to receipt
        header('Location: struk.php?id=' . $penjualan_id);
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get all customer for dropdown
$customer_stmt = $customer->readAll();
$customer_stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
    <style>
        body {
            background: linear-gradient(135deg, #f5e6d3 0%, #e8d5b7 50%, #d4c4a8 100%);
            background-attachment: fixed;
        }
        
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            height: calc(100vh - 120px);
        }
        
        .product-section {
            background: linear-gradient(to bottom, #fff8f0, #fffbf5);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(139, 111, 71, 0.15), 0 0 0 1px rgba(139, 111, 71, 0.1);
            padding: 25px;
            overflow-y: auto;
            border: 2px solid rgba(218, 165, 32, 0.2);
        }
        
        .cart-section {
            background: linear-gradient(to bottom, #fff8f0, #fffbf5);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(139, 111, 71, 0.15), 0 0 0 1px rgba(139, 111, 71, 0.1);
            padding: 25px;
            display: flex;
            flex-direction: column;
            border: 2px solid rgba(218, 165, 32, 0.2);
        }
        
        .cart-section h3 {
            color: #8B6F47;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(139, 111, 71, 0.2);
            text-align: center;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .search-box {
            margin-bottom: 25px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #d4a574;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(139, 111, 71, 0.1);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.2);
        }
        
        .search-box::after {
            content: '🔍';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            pointer-events: none;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 18px;
        }
        
        .product-card {
            border: 2px solid #e8d5b7;
            border-radius: 12px;
            padding: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(139, 111, 71, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .product-card::before {
            content: '🍞';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 60px;
            opacity: 0.05;
            transform: rotate(15deg);
            z-index: 0;
        }
        
        .product-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
            background: linear-gradient(135deg, #fff8f0, #ffe8cc);
            border-bottom: 2px solid #e8d5b7;
            position: relative;
            z-index: 1;
        }
        
        .product-card-content {
            padding: 12px;
            position: relative;
            z-index: 1;
        }
        
        .product-card:hover {
            border-color: #d4a574;
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.3);
            transform: translateY(-3px);
            background: linear-gradient(to bottom, #fffef9, #fff8f0);
        }
        
        .product-card.selected {
            border-color: #8B6F47;
            background: linear-gradient(to bottom, #fff8e8, #ffe8cc);
            box-shadow: 0 6px 20px rgba(139, 111, 71, 0.4);
        }
        
        .product-name {
            font-weight: 700;
            margin-bottom: 8px;
            color: #5a4a3a;
            font-size: 15px;
            line-height: 1.4;
        }
        
        .product-price {
            color: #8B6F47;
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 16px;
        }
        
        .product-stock {
            font-size: 12px;
            color: #8B6F47;
            background: rgba(139, 111, 71, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            font-weight: 500;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            max-height: calc(100vh - 500px);
            padding-right: 8px;
        }
        
        .cart-items::-webkit-scrollbar {
            width: 8px;
        }
        
        .cart-items::-webkit-scrollbar-track {
            background: rgba(232, 213, 183, 0.3);
            border-radius: 10px;
        }
        
        .cart-items::-webkit-scrollbar-thumb {
            background: #d4a574;
            border-radius: 10px;
        }
        
        .cart-items::-webkit-scrollbar-thumb:hover {
            background: #8B6F47;
        }
        
        .cart-item {
            background: white;
            border: 2px solid #e8d5b7;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(139, 111, 71, 0.12);
            display: flex;
            gap: 15px;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #d4a574, #8B6F47);
        }
        
        .cart-item:hover {
            box-shadow: 0 6px 18px rgba(139, 111, 71, 0.2);
            transform: translateY(-2px);
            border-color: #d4a574;
        }
        
        .cart-item-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            background: linear-gradient(135deg, #fff8f0, #ffe8cc);
            flex-shrink: 0;
            border: 2px solid #e8d5b7;
            box-shadow: 0 2px 6px rgba(139, 111, 71, 0.15);
        }
        
        .cart-item-content {
            flex: 1;
            min-width: 0;
        }
        
        .item-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .item-info {
            flex: 1;
            min-width: 0;
        }
        
        .item-name {
            font-weight: 700;
            font-size: 14px;
            color: #5a4a3a;
            margin-bottom: 5px;
            word-wrap: break-word;
        }
        
        .item-details {
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: 12px;
            color: #8B6F47;
        }
        
        .item-price-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .item-price {
            color: #8B6F47;
            font-weight: 500;
        }
        
        .item-diskon-badge {
            background: linear-gradient(135deg, #ffe6e6, #ffd6d6);
            color: #c0392b;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid rgba(192, 57, 43, 0.2);
        }
        
        .item-subtotal {
            font-weight: bold;
            color: #8B6F47;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .item-controls {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
            min-width: 140px;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
        }
        
        .control-label {
            font-size: 10px;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(to bottom, #fff8f0, #ffe8cc);
            border: 2px solid #d4a574;
            border-radius: 8px;
            padding: 3px;
        }
        
        .quantity-control button {
            width: 28px;
            height: 28px;
            border: none;
            background: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            color: #8B6F47;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(139, 111, 71, 0.2);
        }
        
        .quantity-control button:hover {
            background: #d4a574;
            color: white;
            transform: scale(1.1);
        }
        
        .quantity-control button:active {
            transform: scale(0.95);
        }
        
        .quantity-control input {
            width: 45px;
            text-align: center;
            border: none;
            padding: 4px;
            font-size: 13px;
            font-weight: 700;
            background: transparent;
            color: #5a4a3a;
        }
        
        .diskon-input-group {
            display: flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(to bottom, #fff8f0, #ffe8cc);
            border: 2px solid #d4a574;
            border-radius: 8px;
            padding: 4px 8px;
        }
        
        .diskon-input-group input {
            width: 70px;
            border: none;
            padding: 4px;
            font-size: 12px;
            text-align: right;
            background: transparent;
            color: #5a4a3a;
            font-weight: 600;
        }
        
        .btn-remove {
            width: 100%;
            padding: 8px 12px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
        }
        
        .btn-remove:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4);
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #8B6F47;
        }
        
        .empty-cart-icon {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.6;
            filter: drop-shadow(0 2px 4px rgba(139, 111, 71, 0.2));
        }
        
        .empty-cart p {
            font-size: 16px;
            font-weight: 500;
            color: #8B6F47;
        }
        
        .cart-summary {
            border-top: 3px solid rgba(139, 111, 71, 0.3);
            padding-top: 20px;
            background: rgba(255, 248, 240, 0.5);
            border-radius: 10px;
            padding: 20px;
            margin-top: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            color: #5a4a3a;
            font-size: 15px;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 20px;
            color: #8B6F47;
            border-top: 2px solid rgba(139, 111, 71, 0.3);
            padding-top: 15px;
            margin-top: 10px;
            background: linear-gradient(to right, rgba(255, 248, 240, 0.8), rgba(255, 232, 204, 0.8));
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(139, 111, 71, 0.15);
        }
        
        .payment-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(139, 111, 71, 0.2);
        }
        
        .payment-section input,
        .payment-section select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #d4a574;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 12px;
            background: white;
            transition: all 0.3s ease;
            color: #5a4a3a;
        }
        
        .payment-section input:focus,
        .payment-section select:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.2);
        }
        
        .payment-section .form-group {
            margin-bottom: 18px;
        }
        
        .payment-section .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #8B6F47;
            font-size: 14px;
        }
        
        .btn-process {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #8B6F47 0%, #6B5435 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 111, 71, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .btn-process:hover {
            background: linear-gradient(135deg, #6B5435 0%, #5a4530 100%);
            box-shadow: 0 6px 20px rgba(139, 111, 71, 0.5);
            transform: translateY(-3px);
        }
        
        .btn-process:disabled {
            background: linear-gradient(135deg, #bdc3c7, #95a5a6);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        
        .payment-method-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .payment-method-btn {
            flex: 1;
            padding: 14px;
            border: 2px solid #d4a574;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            color: #8B6F47;
            box-shadow: 0 2px 6px rgba(139, 111, 71, 0.1);
        }
        
        .payment-method-btn:hover {
            border-color: #8B6F47;
            background: linear-gradient(to bottom, #fff8f0, #ffe8cc);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(139, 111, 71, 0.2);
        }
        
        .payment-method-btn.active {
            border-color: #8B6F47;
            background: linear-gradient(135deg, #8B6F47, #6B5435);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.4);
        }
        
        .qty-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(to bottom, #fff8f0, #ffe8cc);
            border: 2px solid #d4a574;
            border-radius: 8px;
            padding: 4px;
        }
        
        .qty-btn {
            width: 28px;
            height: 28px;
            border: none;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            color: #8B6F47;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(139, 111, 71, 0.2);
        }
        
        .qty-btn:hover {
            background: #d4a574;
            color: white;
            transform: scale(1.1);
        }
        
        .qty-input {
            width: 50px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 700;
            font-size: 14px;
            color: #5a4a3a;
        }
        
        .cart-item-price {
            font-size: 14px;
            color: #8B6F47;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .cart-item-subtotal {
            font-size: 16px;
            color: #8B6F47;
            font-weight: bold;
            text-align: right;
            min-width: 100px;
        }
        
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }
        
        .btn-remove-item {
            padding: 8px 14px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
        }
        
        .btn-remove-item:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4);
        }
    </style>
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
            <header class="top-nav" style="background: linear-gradient(135deg, #fff8f0 0%, #ffe8cc 100%); border-bottom: 3px solid rgba(139, 111, 71, 0.3);">
                <h1 style="color: #8B6F47; font-weight: 700; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">🍞 POS Roti - Penjualan</h1>
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #d4a574, #8B6F47);">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name" style="color: #8B6F47; font-weight: 600;"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role" style="color: #8B6F47;"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content" style="background: transparent;">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="pos-container">
                    <!-- Product Section -->
                    <div class="product-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Cari roti..." onkeyup="searchProducts()">
                        </div>
                        
                        <div id="productGrid" class="product-grid">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>

                    <!-- Cart Section -->
                    <div class="cart-section">
                        <h3>Keranjang Belanja</h3>
                        
                        <div class="cart-items" id="cartItems">
                            <div class="empty-cart">
                                <div class="empty-cart-icon">🛒</div>
                                <p>Keranjang kosong</p>
                            </div>
                        </div>
                        
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Diskon:</span>
                                <span id="diskonDisplay">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>PPN (<?php echo $ppn_persen; ?>%):</span>
                                <span id="ppn">Rp 0</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="total">Rp 0</span>
                            </div>
                        </div>
                        
                        <div class="payment-section">
                            <div class="form-group">
                                <label for="customer_id">Customer</label>
                                <select id="customer_id" name="customer_id">
                                    <option value="">Pilih customer</option>
                                    <?php 
                                    while ($row = $customer_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_customer']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <div class="payment-method-group">
                                    <div class="payment-method-btn" onclick="selectPaymentMethod('cash')" id="payment-cash">
                                        💵 CASH
                                    </div>
                                    <div class="payment-method-btn" onclick="selectPaymentMethod('qris')" id="payment-qris">
                                        📱 QRIS
                                    </div>
                                    <div class="payment-method-btn" onclick="selectPaymentMethod('transfer')" id="payment-transfer">
                                        🏦 TRANSFER
                                    </div>
                                </div>
                                <input type="hidden" id="metode" name="metode" value="">
                            </div>
                            <div class="form-group">
                                <label for="diskonInput">Diskon (Rp)</label>
                                <input type="number" id="diskonInput" placeholder="Masukkan diskon" value="0" min="0" onkeyup="updateSummary()" onchange="updateSummary()">
                            </div>
                            <div class="form-group" id="paymentInputGroup">
                                <label for="paymentInput">Jumlah Bayar</label>
                                <input type="number" id="paymentInput" placeholder="Masukkan jumlah bayar" value="0" min="0" onkeyup="calculateChange()" onchange="calculateChange()">
                            </div>
                            <div class="form-group" id="transferInputGroup" style="display: none;">
                                <label for="nomorRekeningInput">Nomor Rekening</label>
                                <input type="text" id="nomorRekeningInput" name="nomor_rekening" placeholder="Masukkan nomor rekening" maxlength="50">
                                <small style="color: #8B6F47; font-size: 12px; display: block; margin-top: 5px;">Nomor rekening yang digunakan untuk transfer</small>
                            </div>
                            <div class="summary-row" id="changeRow" style="background: rgba(212, 165, 116, 0.15); padding: 12px; border-radius: 8px; font-weight: 600; color: #8B6F47;">
                                <span>Kembalian:</span>
                                <span id="change" style="font-size: 18px;">Rp 0</span>
                            </div>
                            <input type="text" id="note" placeholder="📝 Catatan (opsional)" style="margin-top: 10px;">
                            <button class="btn-process" id="processBtn" onclick="processTransaction()" disabled>
                                Proses Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];
        let products = [];

        // Load products on page load
        window.onload = function() {
            searchProducts();
        };

        function searchProducts() {
            const keyword = document.getElementById('searchInput').value;
            
            fetch(`penjualan.php?action=search_roti&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    products = data;
                    displayProducts(data);
                })
                .catch(error => console.error('Error:', error));
        }

        function displayProducts(products) {
            const grid = document.getElementById('productGrid');
            grid.innerHTML = '';

            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.onclick = () => addToCart(product);
                
                const imageUrl = product.gambar_roti ? `uploads/${product.gambar_roti}` : 'assets/roti.png';
                
                productCard.innerHTML = `
                    <img src="${imageUrl}" alt="${product.nama_roti}" class="product-image" onerror="this.src='assets/roti.png'">
                    <div class="product-card-content">
                        <div class="product-name">${product.nama_roti}</div>
                        <div class="product-price">${formatCurrency(product.harga_jual)}</div>
                        <div class="product-stock">Stok: ${product.stok} ${product.satuan}</div>
                    </div>
                `;
                
                grid.appendChild(productCard);
            });
        }

        function addToCart(product) {
            if (product.stok <= 0) {
                alert('Stok roti habis!');
                return;
            }

            const existingItem = cart.find(item => item.id === product.id);
            
            if (existingItem) {
                if (existingItem.quantity < product.stok) {
                    existingItem.quantity++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
                // Update diskon jika belum ada atau tetap gunakan yang sudah ada
                if (existingItem.diskon === undefined || existingItem.diskon === 0) {
                    existingItem.diskon = parseFloat(product.diskon || 0);
                }
            } else {
                cart.push({
                    id: product.id,
                    kode_roti: product.kode_roti,
                    nama_roti: product.nama_roti,
                    price: parseFloat(product.harga_jual),
                    diskon: parseFloat(product.diskon || 0),
                    quantity: 1,
                    max_stock: product.stok,
                    gambar_roti: product.gambar_roti || null
                });
            }
            
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartBody = document.getElementById('cartItems');

            if (cart.length === 0) {
                cartBody.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang kosong</p>
                    </div>`;
                updateSummary();
                return;
            }

            let cartHTML = '';

            cart.forEach((item, index) => {
                const subtotal = (item.price * item.quantity) - (item.diskon || 0);
                const imageUrl = item.gambar_roti ? `uploads/${item.gambar_roti}` : 'assets/roti.png';

                cartHTML += `
                    <div class="cart-item">
                        <img src="${imageUrl}" alt="${item.nama_roti}" class="cart-item-image" onerror="this.src='assets/roti.png'">
                        <div class="cart-item-content">
                            <div class="item-name">${item.nama_roti}</div>
                            <div class="cart-item-price">${formatCurrency(item.price)}</div>
                            <div class="qty-selector">
                                <button class="qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                                <input type="number" class="qty-input"
                                    value="${item.quantity}"
                                    min="1"
                                    max="${item.max_stock}"
                                    onchange="setQuantity(${index}, this.value)">
                                <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                        </div>
                        <div class="cart-item-actions">
                            <div class="cart-item-subtotal">${formatCurrency(subtotal)}</div>
                            <button class="btn-remove-item" onclick="removeFromCart(${index})">
                                Hapus
                            </button>
                        </div>
                    </div>
                `;
            });

            cartBody.innerHTML = cartHTML;
            updateSummary();
        }
        
        function selectPaymentMethod(method) {
            // Remove active class from all buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to selected button
            document.getElementById(`payment-${method}`).classList.add('active');
            
            // Set hidden input value
            document.getElementById('metode').value = method;
            
            // If not cash, auto-set payment amount to total and hide change
            if (method !== 'cash') {
                const total = parseFloat(document.getElementById('total').textContent.replace(/[^\d]/g, ''));
                document.getElementById('paymentInput').value = total;
                document.getElementById('paymentInputGroup').style.display = 'none';
                document.getElementById('changeRow').style.display = 'none';
                
                // Show transfer input if method is transfer
                if (method === 'transfer') {
                    document.getElementById('transferInputGroup').style.display = 'block';
                } else {
                    document.getElementById('transferInputGroup').style.display = 'none';
                }
            } else {
                document.getElementById('paymentInputGroup').style.display = 'block';
                document.getElementById('changeRow').style.display = 'flex';
                document.getElementById('paymentInput').value = 0;
                document.getElementById('transferInputGroup').style.display = 'none';
            }
            calculateChange();
        }


        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity >= 1 && newQuantity <= item.max_stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function setQuantity(index, value) {
            const item = cart[index];
            const newQuantity = parseInt(value);
            
            if (newQuantity >= 1 && newQuantity <= item.max_stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function updateItemDiskon(index, diskonValue) {
            const item = cart[index];
            item.diskon = parseFloat(diskonValue) || 0;
            updateCartDisplay();
        }

        function updateSummary() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const ppn = subtotalAfterDiskon * (ppnPersen / 100);
            const total = subtotalAfterDiskon + ppn;
            
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('diskonDisplay').textContent = formatCurrency(diskon);
            document.getElementById('ppn').textContent = formatCurrency(ppn);
            document.getElementById('total').textContent = formatCurrency(total);
            
            calculateChange();
        }

        function calculateChange() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const totalWithPPN = subtotalAfterDiskon * (1 + (ppnPersen / 100));
            const metode = document.getElementById('metode').value;
            const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
            
            let change = 0;
            if (metode === 'cash') {
                change = payment - totalWithPPN;
            } else {
                // For non-cash, no change
                change = 0;
            }
            
            document.getElementById('change').textContent = formatCurrency(Math.max(0, change));
            
            const processBtn = document.getElementById('processBtn');
            const canProcess = cart.length > 0 && (metode !== 'cash' || payment >= totalWithPPN);
            processBtn.disabled = !canProcess || !metode;
        }

        function processTransaction() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const ppn = subtotalAfterDiskon * (ppnPersen / 100);
            const totalWithPPN = subtotalAfterDiskon + ppn;
            let payment = parseFloat(document.getElementById('paymentInput').value) || 0;
            const customerId = document.getElementById('customer_id').value;
            const metode = document.getElementById('metode').value;
            const note = document.getElementById('note').value;
            const nomorRekening = document.getElementById('nomorRekeningInput') ? document.getElementById('nomorRekeningInput').value : '';
            
            if (!metode) {
                alert("Pilih metode pembayaran terlebih dahulu!");
                return;
            }
            
            // Validate transfer method
            if (metode === 'transfer' && !nomorRekening.trim()) {
                alert('Masukkan nomor rekening untuk metode transfer!');
                return;
            }
            
            // For non-cash payments, set payment equal to total
            if (metode !== 'cash') {
                payment = totalWithPPN;
            } else if (payment < totalWithPPN) {
                alert('Jumlah bayar kurang!');
                return;
            }
            
            const change = payment - totalWithPPN;
            
            // Calculate subtotal for each item (with item discount, without PPN)
            cart.forEach(item => {
                item.subtotal = Math.max(0, (item.price * item.quantity) - (item.diskon || 0));
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="process_transaction">
                <input type="hidden" name="items" value='${JSON.stringify(cart)}'>
                <input type="hidden" name="customer_id" value="${customerId}">
                <input type="hidden" name="diskon" value="${diskon}">
                <input type="hidden" name="ppn" value="${ppn}">
                <input type="hidden" name="total_harga" value="${totalWithPPN}">
                <input type="hidden" name="total_bayar" value="${payment}">
                <input type="hidden" name="kembalian" value="${change}">
                <input type="hidden" name="metode" value="${metode}">
                <input type="hidden" name="nomor_rekening" value="${nomorRekening}">
                <input type="hidden" name="note" value="${note}">
            `;
            
            document.body.appendChild(form);
            form.submit();
        }

        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
    </script>
</body>
</html>
