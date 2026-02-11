<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Toggle favorite
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_favorite'])) {
    $product_id = $_POST['product_id'];

    // Check if already in favorites
    $checkStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$_SESSION['user_id'], $product_id]);

    if ($checkStmt->fetch()) {
        // Remove from favorites
        $deleteStmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$_SESSION['user_id'], $product_id]);
    } else {
        // Add to favorites
        $insertStmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
        $insertStmt->execute([$_SESSION['user_id'], $product_id]);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Get user's favorite products
$stmt = $pdo->prepare("
    SELECT p.* FROM products p
    INNER JOIN favorites f ON p.id = f.product_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Produk Favorit Anda</h1>

    <?php if (empty($favorites)): ?>
        <div class="text-center py-12 bg-white rounded-xl shadow-lg">
            <i class="far fa-heart fa-4x text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Belum ada produk favorit</h3>
            <p class="text-gray-600 mb-6">Tambahkan produk iPhone favorit Anda dengan mengklik ikon hati</p>
            <a href="products.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                Jelajahi Produk
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($favorites as $product): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="relative">
                        <img src="assets/images/<?php echo $product['image_url']; ?>"
                            alt="<?php echo $product['name']; ?>"
                            class="w-full h-76 object-cover">
                        <form action="favorites.php" method="POST" class="absolute top-4 right-4">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="toggle_favorite"
                                class="bg-white p-2 rounded-full shadow-lg hover:shadow-xl">
                                <i class="fas fa-heart text-red-500"></i>
                            </button>
                        </form>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2"><?php echo $product['name']; ?></h3>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-2xl font-bold text-blue-600">
                                Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            </span>
                            <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">
                                <?php echo $product['storage']; ?>
                            </span>
                        </div>
                        <div class="flex space-x-2 mb-4">
                            <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">
                                <?php echo $product['color']; ?>
                            </span>
                            <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">
                                <?php echo $product['condition']; ?>
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                                class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>