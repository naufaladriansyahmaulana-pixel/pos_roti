<?php
require_once 'config/config.php';
requireRole(['admin']);

require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $user->username = sanitizeInput($_POST['username']);
                $user->password = $_POST['password'];
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
                $user->email = sanitizeInput($_POST['email']);
                $user->role = sanitizeInput($_POST['role']);

                if ($user->create()) {
                    $message = 'User berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan user!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $user->id = sanitizeInput($_POST['id']);
                $user->username = sanitizeInput($_POST['username']);
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
                $user->email = sanitizeInput($_POST['email']);
                $user->role = sanitizeInput($_POST['role']);

                if ($user->update()) {
                    $message = 'User berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui user!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $user->id = sanitizeInput($_POST['id']);
                if ($user->delete()) {
                    $message = 'User berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus user!';
                    $message_type = 'error';
                }
                break;

            case 'change_password':
                $user->id = sanitizeInput($_POST['id']);
                $new_password = $_POST['new_password'];
                
                if ($user->changePassword($new_password)) {
                    $message = 'Password berhasil diubah!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengubah password!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all users
$stmt = $user->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - <?php echo APP_NAME; ?></title>
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
                <h1>Manajemen User</h1>
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

                <!-- Add User Form -->
                <div class="form-container">
                    <h2>Tambah User Baru</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Kasir</option>
                                    <option value="gudang">Gudang</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Tambah User</button>
                    </form>
                </div>

                <!-- Data User Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar User</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['email'] ?: '-'; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $row['role'] === 'admin' ? 'badge-danger' : 
                                            ($row['role'] === 'kasir' ? 'badge-success' : 'badge-warning'); 
                                    ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="btn btn-warning btn-sm">Edit</button>
                                    <button onclick="changePassword(<?php echo $row['id']; ?>, '<?php echo $row['username']; ?>')" 
                                            class="btn btn-info btn-sm">Password</button>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="deleteUser(<?php echo $row['id']; ?>)" 
                                            class="btn btn-danger btn-sm">Hapus</button>
                                    <?php endif; ?>
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
            <h2>Edit User</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                            <option value="gudang">Gudang</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 400px;">
            <h2>Ubah Password</h2>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="id" id="password_user_id">
                
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" id="password_username" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                    <button type="button" onclick="closePasswordModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function changePassword(id, username) {
            document.getElementById('password_user_id').value = id;
            document.getElementById('password_username').value = username;
            document.getElementById('new_password').value = '';
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function deleteUser(id) {
            if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
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

        document.getElementById('passwordModal').onclick = function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        }
    </script>
</body>
</html>
