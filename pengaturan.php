<?php
require_once 'config/config.php';
requireRole(['admin']);

require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$pengaturan = new Pengaturan($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update') {
    $settings = [
        'nama_toko' => sanitizeInput($_POST['nama_toko'] ?? ''),
        'alamat_toko' => sanitizeInput($_POST['alamat_toko'] ?? ''),
        'telepon_toko' => sanitizeInput($_POST['telepon_toko'] ?? ''),
        'email_toko' => sanitizeInput($_POST['email_toko'] ?? ''),
        'nomor_rekening' => sanitizeInput($_POST['nomor_rekening'] ?? ''),
        'ppn_persen' => sanitizeInput($_POST['ppn_persen'] ?? '10'),
        'footer_struk' => sanitizeInput($_POST['footer_struk'] ?? ''),
        'warna_primary' => sanitizeInput($_POST['warna_primary'] ?? '#667eea'),
        'warna_secondary' => sanitizeInput($_POST['warna_secondary'] ?? '#764ba2'),
        'warna_sidebar' => sanitizeInput($_POST['warna_sidebar'] ?? '#2c3e50'),
        'warna_sidebar_header' => sanitizeInput($_POST['warna_sidebar_header'] ?? '#34495e'),
        'warna_success' => sanitizeInput($_POST['warna_success'] ?? '#27ae60'),
        'warna_danger' => sanitizeInput($_POST['warna_danger'] ?? '#e74c3c'),
        'warna_warning' => sanitizeInput($_POST['warna_warning'] ?? '#f39c12'),
        'warna_info' => sanitizeInput($_POST['warna_info'] ?? '#3498db')
    ];
    
    if ($pengaturan->updateAll($settings)) {
        $message = 'Pengaturan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui pengaturan!';
        $message_type = 'error';
    }
}

// Get all settings
$settings = $pengaturan->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Aplikasi - <?php echo APP_NAME; ?></title>
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
                <h1>Pengaturan Aplikasi</h1>
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

                <!-- Pengaturan Form -->
                <div class="form-container">
                    <h2>Pengaturan Umum</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="form-group">
                            <label for="nama_toko">Nama Toko</label>
                            <input type="text" id="nama_toko" name="nama_toko" 
                                   value="<?php echo htmlspecialchars($settings['nama_toko'] ?? APP_NAME); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="alamat_toko">Alamat Toko</label>
                            <textarea id="alamat_toko" name="alamat_toko" rows="3"><?php echo htmlspecialchars($settings['alamat_toko'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telepon_toko">Telepon</label>
                                <input type="text" id="telepon_toko" name="telepon_toko" 
                                       value="<?php echo htmlspecialchars($settings['telepon_toko'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email_toko">Email</label>
                                <input type="email" id="email_toko" name="email_toko" 
                                       value="<?php echo htmlspecialchars($settings['email_toko'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening Bank (untuk Transfer)</label>
                            <input type="text" id="nomor_rekening" name="nomor_rekening" 
                                   value="<?php echo htmlspecialchars($settings['nomor_rekening'] ?? ''); ?>"
                                   placeholder="Contoh: 1234567890 (BCA) atau 9876543210 (Mandiri)">
                            <small style="color: #8B6F47; display: block; margin-top: 5px;">
                                Nomor rekening yang akan ditampilkan di struk untuk metode pembayaran transfer
                            </small>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e1e1e1;">

                        <h3 style="margin-bottom: 20px;">Pengaturan Pajak</h3>
                        
                        <div class="form-group">
                            <label for="ppn_persen">PPN (%)</label>
                            <input type="number" id="ppn_persen" name="ppn_persen" 
                                   value="<?php echo htmlspecialchars($settings['ppn_persen'] ?? '10'); ?>" 
                                   min="0" max="100" step="0.1" required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Persentase Pajak Pertambahan Nilai (PPN) yang akan diterapkan pada setiap transaksi penjualan
                            </small>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e1e1e1;">

                        <h3 style="margin-bottom: 20px;">Pengaturan Struk</h3>
                        
                        <div class="form-group">
                            <label for="footer_struk">Footer Struk</label>
                            <textarea id="footer_struk" name="footer_struk" rows="3" 
                                      placeholder="Contoh: Terima kasih atas kunjungan Anda!"><?php echo htmlspecialchars($settings['footer_struk'] ?? 'Terima kasih atas kunjungan Anda!'); ?></textarea>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Teks yang akan ditampilkan di bagian bawah struk
                            </small>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e1e1e1;">

                        <h3 style="margin-bottom: 20px;">Pengaturan Warna Tema</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_primary">Warna Primary</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_primary" name="warna_primary" 
                                           value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#667eea'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#667eea'); ?>" 
                                           onchange="document.getElementById('warna_primary').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Warna utama untuk tombol, link, dan elemen aktif
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="warna_secondary">Warna Secondary</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_secondary" name="warna_secondary" 
                                           value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#764ba2'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#764ba2'); ?>" 
                                           onchange="document.getElementById('warna_secondary').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Warna sekunder untuk gradient dan accent
                                </small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_sidebar">Warna Sidebar</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_sidebar" name="warna_sidebar" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#2c3e50'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#2c3e50'); ?>" 
                                           onchange="document.getElementById('warna_sidebar').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_sidebar_header">Warna Header Sidebar</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_sidebar_header" name="warna_sidebar_header" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#34495e'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#34495e'); ?>" 
                                           onchange="document.getElementById('warna_sidebar_header').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_success">Warna Success</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_success" name="warna_success" 
                                           value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>" 
                                           onchange="document.getElementById('warna_success').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_danger">Warna Danger</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_danger" name="warna_danger" 
                                           value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>" 
                                           onchange="document.getElementById('warna_danger').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_warning">Warna Warning</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_warning" name="warna_warning" 
                                           value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>" 
                                           onchange="document.getElementById('warna_warning').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_info">Warna Info</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_info" name="warna_info" 
                                           value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>" 
                                           onchange="document.getElementById('warna_info').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                            <strong>Tips:</strong> Gunakan color picker untuk memilih warna atau masukkan kode hex (contoh: #667eea). 
                            Perubahan warna akan langsung diterapkan setelah menyimpan.
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sync color picker dengan text input
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            colorInput.addEventListener('input', function() {
                const textInput = this.parentElement.querySelector('input[type="text"]');
                if (textInput) {
                    textInput.value = this.value;
                }
            });
        });

        // Sync text input dengan color picker
        document.querySelectorAll('input[type="text"]').forEach(textInput => {
            if (textInput.previousElementSibling && textInput.previousElementSibling.type === 'color') {
                textInput.addEventListener('input', function() {
                    const colorInput = this.parentElement.querySelector('input[type="color"]');
                    if (colorInput && /^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorInput.value = this.value;
                    }
                });
            }
        });
    </script>
</body>
</html>

