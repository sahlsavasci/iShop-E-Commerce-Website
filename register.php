<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert new user - SIMPAN PASSWORD PLAIN
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password]);

                $success = 'Registrasi berhasil! Silakan login.';
                
                // Auto login setelah register (optional)
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                
                header("Refresh: 2; URL=index.php");
                
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-8">Daftar Akun Baru</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
                <p>Redirecting to home page...</p>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Konfirmasi Password</label>
                <input type="password" name="confirm_password" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <button type="submit" 
                    class="w-full bg-green-600 text-white py-3 rounded-lg text-lg font-semibold hover:bg-green-700">
                Daftar
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Sudah punya akun? 
                <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Login di sini
                </a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>