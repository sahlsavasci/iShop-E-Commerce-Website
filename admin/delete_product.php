<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: manage_products.php');
    exit();
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: manage_products.php');
    exit();
}

// Handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // Delete product image if exists
        if ($product['image_url']) {
            $imagePath = '../assets/images/' . $product['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete from favorites
        $pdo->prepare("DELETE FROM favorites WHERE product_id = ?")->execute([$product_id]);
        
        // Delete product
        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        
        if ($deleteStmt->execute([$product_id])) {
            $_SESSION['success'] = "Product deleted successfully!";
            header('Location: manage_products.php');
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete product.";
            header('Location: manage_products.php');
            exit();
        }
    } else {
        // Cancel delete
        header('Location: manage_products.php');
        exit();
    }
}

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800">Delete Product</h1>
            <p class="text-gray-600">Are you sure you want to delete this product?</p>
        </div>

        <!-- Product Info -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <?php if ($product['image_url']): ?>
                    <div class="relative h-48 mb-6 rounded-lg overflow-hidden">
                        <img src="../assets/images/<?php echo $product['image_url']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="h-48 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg mb-6 flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-gray-400 text-6xl"></i>
                    </div>
                <?php endif; ?>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <div class="mb-4">
                    <span class="text-xl font-bold text-blue-600">
                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-gray-500 text-sm">Storage</div>
                        <div class="font-semibold"><?php echo htmlspecialchars($product['storage']); ?></div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-gray-500 text-sm">Color</div>
                        <div class="font-semibold"><?php echo htmlspecialchars($product['color']); ?></div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium px-3 py-1 rounded-full bg-gray-100 text-gray-800">
                        Stock: <?php echo $product['stock']; ?>
                    </span>
                    <span class="text-sm text-gray-600">
                        <?php echo htmlspecialchars($product['product_condition']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Warning Message -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
            <div class="flex items-center mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                <h3 class="text-lg font-semibold text-red-800">Warning</h3>
            </div>
            <p class="text-red-700 mb-4">
                This action cannot be undone. All product data including images will be permanently deleted.
            </p>
            <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                <li>Product information will be removed</li>
                <li>Product image will be deleted</li>
                <li>Product will be removed from favorites</li>
                <li>This action is irreversible</li>
            </ul>
        </div>

        <!-- Delete Form -->
        <form method="POST" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="manage_products.php" 
                   class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-medium text-center">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        name="confirm_delete"
                        class="flex-1 bg-gradient-to-r from-red-600 to-red-700 text-white px-6 py-3 rounded-lg hover:from-red-700 hover:to-red-800 font-medium transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-trash mr-2"></i>Delete Product
                </button>
            </div>
            
            <div class="flex items-center justify-center">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="confirm" 
                           required
                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">
                        I understand this action cannot be undone
                    </span>
                </label>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>