<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Roti.php';
require_once 'models/KategoriRoti.php';

$database = new Database();
$db = $database->getConnection();

$roti = new Roti($db);
$kategori = new KategoriRoti($db);

$message = '';
$message_type = '';

// Handle file upload
function uploadGambar($file, $old_file = null) {
    if (!isset($file['name']) || empty($file['name'])) {
        return $old_file; // Return old file if no new file uploaded
    }
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return null;
    }
    
    if ($file['size'] > $max_size) {
        return null;
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('roti_', true) . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Delete old file if exists
        if ($old_file && file_exists($upload_dir . $old_file)) {
            unlink($upload_dir . $old_file);
        }
        return $new_filename;
    }
    
    return null;
}

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $roti->kode_roti = sanitizeInput($_POST['kode_roti']);
                $roti->nama_roti = sanitizeInput($_POST['nama_roti']);
                $roti->kategori_id = sanitizeInput($_POST['kategori_id']);
                $roti->satuan = sanitizeInput($_POST['satuan']);
                $roti->harga_beli = sanitizeInput($_POST['harga_beli']);
                $roti->harga_jual = sanitizeInput($_POST['harga_jual']);
                $roti->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $roti->stok = sanitizeInput($_POST['stok']);
                $roti->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $roti->tanggal_expired = sanitizeInput($_POST['tanggal_expired']);
                $roti->deskripsi = sanitizeInput($_POST['deskripsi']);
                
                // Handle image upload
                if (isset($_FILES['foto_roti']) && $_FILES['foto_roti']['error'] === UPLOAD_ERR_OK) {
                    $uploaded_file = uploadGambar($_FILES['foto_roti']);
                    $roti->gambar_roti = $uploaded_file ? $uploaded_file : '';
                } else {
                    $roti->gambar_roti = '';
                }

                if ($roti->create()) {
                    $message = 'Data roti berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan data roti!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $roti->id = sanitizeInput($_POST['id']);
                
                // Get current gambar_roti first
                $roti->readOne();
                $old_gambar = $roti->gambar_roti;
                
                $roti->kode_roti = sanitizeInput($_POST['kode_roti']);
                $roti->nama_roti = sanitizeInput($_POST['nama_roti']);
                $roti->kategori_id = sanitizeInput($_POST['kategori_id']);
                $roti->satuan = sanitizeInput($_POST['satuan']);
                $roti->harga_beli = sanitizeInput($_POST['harga_beli']);
                $roti->harga_jual = sanitizeInput($_POST['harga_jual']);
                $roti->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $roti->stok = sanitizeInput($_POST['stok']);
                $roti->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $roti->tanggal_expired = sanitizeInput($_POST['tanggal_expired']);
                $roti->deskripsi = sanitizeInput($_POST['deskripsi']);
                
                // Handle image upload
                if (isset($_FILES['foto_roti']) && $_FILES['foto_roti']['error'] === UPLOAD_ERR_OK) {
                    $uploaded_file = uploadGambar($_FILES['foto_roti'], $old_gambar);
                    $roti->gambar_roti = $uploaded_file ? $uploaded_file : $old_gambar;
                } else {
                    // Keep old image if no new file uploaded
                    $roti->gambar_roti = $old_gambar;
                }

                if ($roti->update()) {
                    $message = 'Data roti berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui data roti!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $roti->id = sanitizeInput($_POST['id']);
                if ($roti->delete()) {
                    $message = 'Data roti berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus data roti!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all roti and kategori
$stmt = $roti->readAll();
$kategori_stmt = $kategori->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data roti - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php 
        $role = $_SESSION['user_role'] ?? '';
        require_once 'sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <h1>Data roti</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo isset($_SESSION['nama_lengkap']) ? strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) : ''; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?></div>
                        <div class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['user_role'] ?? '')); ?></div>
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

                <!-- Add roti Form -->
                <div class="form-container">
                    <h2>Tambah roti Baru</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode_roti">Kode roti</label>
                                <input type="text" id="kode_roti" name="kode_roti" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_roti">Nama roti</label>
                                <input type="text" id="nama_roti" name="nama_roti" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori_id">Kategori</label>
                                <select id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php 
                                    // gunakan variabel terpisah agar tidak menimpa $row utama
                                    $kategori_options = $kategori->readAll();
                                    while ($k = $kategori_options->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo htmlspecialchars($k['id']); ?>"><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <input type="text" id="satuan" name="satuan" required placeholder="cth: pcs">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="harga_beli">Harga Beli</label>
                                <input type="number" id="harga_beli" name="harga_beli" required min="0" step="100">
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual</label>
                                <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="diskon">Diskon Default (Rp)</label>
                                <input type="number" id="diskon" name="diskon" min="0" step="100" value="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stok">Stok</label>
                                <input type="number" id="stok" name="stok" required min="0">
                            </div>
                            <div class="form-group">
                                <label for="stok_minimum">Stok Minimum</label>
                                <input type="number" id="stok_minimum" name="stok_minimum" required min="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tanggal_expired">Tanggal Expired</label>
                                <input type="date" id="tanggal_expired" name="tanggal_expired">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="foto_roti">Foto roti (opsional)</label>
                            <input type="file" id="foto_roti" name="foto_roti" accept="image/jpeg,image/png,image/gif,image/webp">
                            <small style="color: #8B6F47; font-size: 12px;">Format: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                            <div id="preview-container" style="margin-top: 10px; display: none;">
                                <img id="preview-image" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #d4a574;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah roti</button>
                    </form>
                </div>

                <!-- Data roti Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar roti</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama roti</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Diskon</th>
                                <th>Stok</th>
                                <th>Stok Min</th>
                                <th>Expired</th>
                                <th>Foto Roti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kode_roti']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_roti']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['satuan']); ?></td>
                                <td><?php echo formatCurrency($row['harga_beli']); ?></td>
                                <td><?php echo formatCurrency($row['harga_jual']); ?></td>
                                <td><?php echo formatCurrency($row['diskon'] ?? 0); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['stok'] <= $row['stok_minimum']) ? 'badge-danger' : 'badge-success'; ?>">
                                        <?php echo htmlspecialchars($row['stok']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['stok_minimum']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($row['tanggal_expired'])) {
                                        $expired = strtotime($row['tanggal_expired']);
                                        $now = time();
                                        $days_left = ($expired - $now) / (60 * 60 * 24);
                                        
                                        if ($days_left < 0) {
                                            echo '<span class="badge badge-danger">Expired</span>';
                                        } elseif ($days_left <= 30) {
                                            echo '<span class="badge badge-warning">' . date('d/m/Y', $expired) . '</span>';
                                        } else {
                                            echo date('d/m/Y', $expired);
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['gambar_roti'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($row['gambar_roti']); ?>" alt="Foto roti" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick='editroti(<?php echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' 
                                            class="btn btn-warning btn-sm">Edit</button>
                                    <button onclick="deleteroti(<?php echo (int)$row['id']; ?>)" 
                                            class="btn btn-danger btn-sm">Hapus</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto;">
            <h2>Edit roti</h2>
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_kode_roti">Kode roti</label>
                        <input type="text" id="edit_kode_roti" name="kode_roti" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_roti">Nama roti</label>
                        <input type="text" id="edit_nama_roti" name="nama_roti" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_kategori_id">Kategori</label>
                        <select id="edit_kategori_id" name="kategori_id" required>
                            <?php 
                            // Ambil ulang daftar kategori untuk select di modal (variabel terpisah)
                            $kategori_options = $kategori->readAll();
                            while ($k = $kategori_options->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <option value="<?php echo htmlspecialchars($k['id']); ?>"><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_satuan">Satuan</label>
                        <input type="text" id="edit_satuan" name="satuan" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_harga_beli">Harga Beli</label>
                        <input type="number" id="edit_harga_beli" name="harga_beli" required min="0" step="100">
                    </div>
                    <div class="form-group">
                        <label for="edit_harga_jual">Harga Jual</label>
                        <input type="number" id="edit_harga_jual" name="harga_jual" required min="0" step="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_diskon">Diskon Default (Rp)</label>
                        <input type="number" id="edit_diskon" name="diskon" min="0" step="100" value="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_stok">Stok</label>
                        <input type="number" id="edit_stok" name="stok" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="edit_stok_minimum">Stok Minimum</label>
                        <input type="number" id="edit_stok_minimum" name="stok_minimum" required min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_tanggal_expired">Tanggal Expired</label>
                        <input type="date" id="edit_tanggal_expired" name="tanggal_expired">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_deskripsi">Deskripsi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_foto_roti">Foto roti (kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" id="edit_foto_roti" name="foto_roti" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small style="color: #8B6F47; font-size: 12px;">Format: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                    <div id="edit-preview-container" style="margin-top: 10px;">
                        <img id="edit-preview-image" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #d4a574; display: none;">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editroti(data) {
            // Pastikan data aman (cek null)
            document.getElementById('edit_id').value = data.id || '';
            document.getElementById('edit_kode_roti').value = data.kode_roti || '';
            document.getElementById('edit_nama_roti').value = data.nama_roti || '';
            document.getElementById('edit_kategori_id').value = data.kategori_id || '';
            document.getElementById('edit_satuan').value = data.satuan || '';
            document.getElementById('edit_harga_beli').value = data.harga_beli || '';
            document.getElementById('edit_harga_jual').value = data.harga_jual || '';
            document.getElementById('edit_diskon').value = data.diskon || 0;
            document.getElementById('edit_stok').value = data.stok || '';
            document.getElementById('edit_stok_minimum').value = data.stok_minimum || '';
            document.getElementById('edit_tanggal_expired').value = data.tanggal_expired || '';
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';
            
            // Show current image if exists
            const previewImg = document.getElementById('edit-preview-image');
            if (data.gambar_roti) {
                previewImg.src = 'uploads/' + data.gambar_roti;
                previewImg.style.display = 'block';
            } else {
                previewImg.style.display = 'none';
            }
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        // Preview image on file select
        document.getElementById('foto_roti')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('preview-container').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        document.getElementById('edit_foto_roti')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-preview-image').src = e.target.result;
                    document.getElementById('edit-preview-image').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteroti(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data roti ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').onclick = function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        };
    </script>
</body>
</html>
