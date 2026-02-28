<?php
require_once 'config/config.php';
requireRole(['admin']);

require_once 'models/Vendor.php';

$database = new Database();
$db = $database->getConnection();

$vendor = new Vendor($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $vendor->nama_vendor = sanitizeInput($_POST['nama_vendor']);
                $vendor->alamat = sanitizeInput($_POST['alamat']);
                $vendor->telepon = sanitizeInput($_POST['telepon']);
                $vendor->email = sanitizeInput($_POST['email']);

                if ($vendor->create()) {
                    $message = 'vendor berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan vendor!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $vendor->id = sanitizeInput($_POST['id']);
                $vendor->nama_vendor = sanitizeInput($_POST['nama_vendor']);
                $vendor->alamat = sanitizeInput($_POST['alamat']);
                $vendor->telepon = sanitizeInput($_POST['telepon']);
                $vendor->email = sanitizeInput($_POST['email']);

                if ($vendor->update()) {
                    $message = 'vendor berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui vendor!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $vendor->id = sanitizeInput($_POST['id']);
                if ($vendor->delete()) {
                    $message = 'vendor berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus vendor!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all vendor
$stmt = $vendor->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vendor - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <h1>vendor</h1>
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

                <!-- Add vendor Form -->
                <div class="form-container">
                    <h2>Tambah vendor Baru</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_vendor">Nama vendor</label>
                                <input type="text" id="nama_vendor" name="nama_vendor" required>
                            </div>
                            <div class="form-group">
                                <label for="telepon">Telepon</label>
                                <input type="text" id="telepon" name="telepon">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Tambah vendor</button>
                    </form>
                </div>

                <!-- Data vendor Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar vendor</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama vendor</th>
                                <th>Alamat</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nama_vendor']; ?></td>
                                <td><?php echo $row['alamat'] ?: '-'; ?></td>
                                <td><?php echo $row['telepon'] ?: '-'; ?></td>
                                <td><?php echo $row['email'] ?: '-'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button onclick="editvendor(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="btn btn-warning btn-sm">Edit</button>
                                    <button onclick="deletevendor(<?php echo $row['id']; ?>)" 
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
            <h2>Edit vendor</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_nama_vendor">Nama vendor</label>
                        <input type="text" id="edit_nama_vendor" name="nama_vendor" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_telepon">Telepon</label>
                        <input type="text" id="edit_telepon" name="telepon">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_alamat">Alamat</label>
                    <textarea id="edit_alamat" name="alamat" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editvendor(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nama_vendor').value = data.nama_vendor;
            document.getElementById('edit_alamat').value = data.alamat;
            document.getElementById('edit_telepon').value = data.telepon;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deletevendor(id) {
            if (confirm('Apakah Anda yakin ingin menghapus vendor ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
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
        }
    </script>
</body>
</html>
