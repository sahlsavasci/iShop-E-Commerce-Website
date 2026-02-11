<?php
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = $_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Check if product is in favorites for logged in user
$isFavorite = false;
if (isLoggedIn() && $_SESSION['role'] == 'user') {
    $favStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $favStmt->execute([$_SESSION['user_id'], $product_id]);
    $isFavorite = $favStmt->fetch();
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Product Image -->
            <div class="md:w-1/2 p-8">
                <img src="assets/images/<?php echo $product['image_url']; ?>"
                    alt="<?php echo $product['name']; ?>"
                    class="w-full h-auto rounded-lg">
            </div>

            <!-- Product Details -->
            <div class="md:w-1/2 p-8">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-3xl font-bold"><?php echo $product['name']; ?></h1>
                    <?php if (isLoggedIn() && $_SESSION['role'] == 'user'): ?>
                        <form action="favorites.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="toggle_favorite"
                                class="text-2xl <?php echo $isFavorite ? 'text-red-500' : 'text-gray-300'; ?> 
                                           hover:text-red-500">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="text-4xl font-bold text-blue-600 mb-6">
                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                </div>

                <div class="mb-6">
                    <p class="text-gray-700 text-lg"><?php echo $product['description']; ?></p>
                </div>

                <!-- Specifications -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-gray-500 text-sm">Storage</div>
                        <div class="font-semibold"><?php echo $product['storage']; ?></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-gray-500 text-sm">Warna</div>
                        <div class="font-semibold"><?php echo $product['color']; ?></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-gray-500 text-sm">Kondisi</div>
                        <div class="font-semibold"><?php echo $product['product_condition']; ?></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-gray-500 text-sm">Stok</div>
                        <div class="font-semibold <?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' unit' : 'Habis'; ?>
                        </div>
                    </div>
                </div>

                <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                    <!-- Add to cart button (placeholder for future implementation) -->
                    <button class="w-full bg-green-600 text-white py-4 rounded-lg text-lg font-semibold 
                                  hover:bg-green-700 transition duration-300">
                        <i class="fas fa-shopping-cart mr-2"></i>Tambahkan ke Keranjang
                    </button>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-yellow-800 mb-2">Silakan login untuk membeli produk ini</p>
                        <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                            Login di sini
                        </a>
                    </div>
                <?php else: ?>
                    <button class="w-full bg-gray-400 text-white py-4 rounded-lg text-lg font-semibold cursor-not-allowed">
                        Stok Habis
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>