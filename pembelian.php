<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Pembelian.php';
require_once 'models/Vendor.php';
require_once 'models/Roti.php';

$database = new Database();
$db = $database->getConnection();

$pembelian = new Pembelian($db);
$vendor = new Vendor($db);
$roti = new Roti($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $db->beginTransaction();
                    
                    // Create pembelian record
                    $pembelian->no_faktur = $pembelian->generateNoFaktur();
                    $pembelian->vendor_id = sanitizeInput($_POST['vendor_id']);
                    $pembelian->user_id = $_SESSION['user_id'];
                    $pembelian->total_harga = sanitizeInput($_POST['total_harga']);
                    $pembelian->tanggal_pembelian = sanitizeInput($_POST['tanggal_pembelian']);
                    $pembelian->status = 'pending';
                    
                    $pembelian_id = $pembelian->create();
                    
                    if (!$pembelian_id) {
                        throw new Exception('Gagal membuat pembelian');
                    }
                    
                    // Process detail pembelian
                    $items = json_decode($_POST['items'], true);
                    foreach ($items as $item) {
                        // Insert detail pembelian
                        $detail_query = "INSERT INTO detail_pembelian (pembelian_id, roti_id, jumlah, harga_satuan, subtotal) 
                                         VALUES (:pembelian_id, :roti_id, :jumlah, :harga_satuan, :subtotal)";
                        $detail_stmt = $db->prepare($detail_query);
                        $detail_stmt->bindParam(':pembelian_id', $pembelian_id);
                        $detail_stmt->bindParam(':roti_id', $item['id']);
                        $detail_stmt->bindParam(':jumlah', $item['quantity']);
                        $detail_stmt->bindParam(':harga_satuan', $item['price']);
                        $detail_stmt->bindParam(':subtotal', $item['subtotal']);
                        
                        if (!$detail_stmt->execute()) {
                            throw new Exception('Gagal menyimpan detail pembelian');
                        }
                    }
                    
                    $db->commit();
                    $message = 'Pembelian berhasil ditambahkan!';
                    $message_type = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
                
            case 'complete':
                $pembelian_id = sanitizeInput($_POST['id']);
                
                try {
                    $db->beginTransaction();
                    
                    // Update status to completed
                    $pembelian->updateStatus($pembelian_id, 'completed');
                    
                    // Update stock
                    $detail_stmt = $pembelian->getDetailPembelian($pembelian_id);
                    while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $roti->updateStok($row['roti_id'], $row['jumlah']);
                    }
                    
                    $db->commit();
                    $message = 'Pembelian berhasil diselesaikan!';
                    $message_type = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all pembelian
$stmt = $pembelian->readAll();

// Get all vendor for dropdown
$vendor_stmt = $vendor->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian - <?php echo APP_NAME; ?></title>
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
                <h1>Pembelian roti</h1>
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
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Pembelian Form -->
                <div class="form-container">
                    <h2>Tambah Pembelian Baru</h2>
                    <form method="POST" id="pembelianForm">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="items" id="itemsInput">
                        <input type="hidden" name="total_harga" id="totalHargaInput">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="vendor_id">vendor</label>
                                <select id="vendor_id" name="vendor_id" required>
                                    <option value="">Pilih vendor</option>
                                    <?php while ($row = $vendor_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_vendor']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_pembelian">Tanggal Pembelian</label>
                                <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="form-group">
                            <label>Items Pembelian</label>
                            <div id="itemsContainer">
                                <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    <select class="roti-select" onchange="loadrotiInfo(this)">
                                        <option value="">Pilih roti</option>
                                        <?php 
                                        $roti_stmt = $roti->readAll();
                                        while ($row = $roti_stmt->fetch(PDO::FETCH_ASSOC)): 
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" 
                                                    data-harga="<?php echo $row['harga_beli']; ?>"
                                                    data-nama="<?php echo $row['nama_roti']; ?>">
                                                <?php echo $row['nama_roti']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="number" class="quantity-input" placeholder="Jumlah" min="1" onchange="calculateSubtotal(this)">
                                    <input type="number" class="price-input" placeholder="Harga" min="0" step="100" onchange="calculateSubtotal(this)">
                                    <input type="number" class="subtotal-input" placeholder="Subtotal" readonly>
                                    <button type="button" onclick="removeItem(this)" class="btn btn-danger btn-sm">Hapus</button>
                                </div>
                            </div>
                            <button type="button" onclick="addItem()" class="btn btn-success">Tambah Item</button>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Total Harga:</label>
                                <input type="text" id="totalDisplay" value="Rp 0" readonly style="font-weight: bold; font-size: 16px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                    </form>
                </div>

                <!-- Data Pembelian Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Pembelian</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>vendor</th>
                                <th>Total Harga</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['no_faktur']; ?></td>
                                <td><?php echo $row['nama_vendor']; ?></td>
                                <td><?php echo formatCurrency($row['total_harga']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pembelian'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['status'] === 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Konfirmasi penerimaan roti?')">
                                                Terima
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button onclick="viewDetail(<?php echo $row['id']; ?>)" 
                                            class="btn btn-info btn-sm">Detail</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let itemCount = 1;

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'item-row';
            newItem.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;';
            
            newItem.innerHTML = `
                <select class="roti-select" onchange="loadrotiInfo(this)">
                    <option value="">Pilih roti</option>
                    ${document.querySelector('.roti-select').innerHTML}
                </select>
                <input type="number" class="quantity-input" placeholder="Jumlah" min="1" onchange="calculateSubtotal(this)">
                <input type="number" class="price-input" placeholder="Harga" min="0" step="100" onchange="calculateSubtotal(this)">
                <input type="number" class="subtotal-input" placeholder="Subtotal" readonly>
                <button type="button" onclick="removeItem(this)" class="btn btn-danger btn-sm">Hapus</button>
            `;
            
            container.appendChild(newItem);
            itemCount++;
        }

        function removeItem(button) {
            if (document.querySelectorAll('.item-row').length > 1) {
                button.parentElement.remove();
                calculateTotal();
            } else {
                alert('Minimal harus ada 1 item');
            }
        }

        function loadrotiInfo(select) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                const priceInput = select.parentElement.querySelector('.price-input');
                priceInput.value = option.dataset.harga;
                calculateSubtotal(select);
            }
        }

        function calculateSubtotal(element) {
            const row = element.parentElement;
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = quantity * price;
            
            row.querySelector('.subtotal-input').value = subtotal;
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            const items = [];
            
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('.roti-select');
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const subtotal = quantity * price;
                
                if (select.value && quantity > 0 && price > 0) {
                    total += subtotal;
                    items.push({
                        id: select.value,
                        quantity: quantity,
                        price: price,
                        subtotal: subtotal
                    });
                }
            });
            
            document.getElementById('totalDisplay').value = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalHargaInput').value = total;
            document.getElementById('itemsInput').value = JSON.stringify(items);
        }

        function viewDetail(id) {
            // Implement detail view modal or redirect
            alert('Detail pembelian ID: ' + id);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotal();
        });
    </script>
</body>
</html>
