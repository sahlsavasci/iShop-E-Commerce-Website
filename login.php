<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Email atau password salah!';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto">
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-8">Login</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    value="admin@ishop.com">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    value="admin123">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg text-lg font-semibold hover:bg-blue-700">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Belum punya akun?
                <a href="register.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Daftar di sini
                </a>
            </p>
        </div>

        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold mb-2">Credentials:</h4>
            <p class="text-sm text-gray-600 mb-1"><strong>Admin:</strong> admin@ishop.com / admin123</p>
            <p class="text-sm text-gray-600"><strong>User:</strong> user@ishop.com / user123</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>