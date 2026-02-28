<?php
require_once 'config/config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        
        if ($user->login($username, $password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['nama_lengkap'] = $user->nama_lengkap;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['email'] = $user->email;
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Username atau password salah!';
        }
    } else {
        $error_message = 'Username dan password harus diisi!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Silakan masuk dengan akun Anda</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Masuk</button>
            </form>

            <div class="login-footer">
                <p><strong>Demo Account:</strong></p>
                <p>Admin: admin / password</p>
                <p>Kasir: kasir1 / password</p>
                <p>Gudang: gudang1 / password</p>
                <p style="margin-top: 15px;">
                    <a href="index.php" style="color: #007bff; text-decoration: none;">← Kembali ke Beranda</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
