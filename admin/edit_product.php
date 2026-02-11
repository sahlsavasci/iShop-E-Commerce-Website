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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $storage = $_POST['storage'];
    $color = $_POST['color'];
    $condition = $_POST['product_condition']; // Perbaikan: Sesuai nama kolom
    $stock = $_POST['stock'];
    
    // Handle image upload
    $image_url = $product['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Delete old image if exists
                if ($image_url && file_exists($uploadDir . $image_url)) {
                    unlink($uploadDir . $image_url);
                }
                $image_url = $fileName;
            }
        }
    }
    
    // Update product
    $updateStmt = $pdo->prepare("
        UPDATE products 
        SET name = ?, price = ?, description = ?, storage = ?, 
            color = ?, product_condition = ?, stock = ?, image_url = ?
        WHERE id = ?
    ");
    
    if ($updateStmt->execute([$name, $price, $description, $storage, $color, $condition, $stock, $image_url, $product_id])) {
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: edit_product.php?id=$product_id");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update product.";
    }
}

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Product</h1>
                    <p class="text-gray-600">Update product information</p>
                </div>
                <a href="manage_products.php" 
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <form method="POST" enctype="multipart/form-data" class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Product Name -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-tag mr-2"></i>Product Name *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   required>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-money-bill-wave mr-2"></i>Price (Rp) *
                            </label>
                            <input type="number" 
                                   name="price" 
                                   value="<?php echo $product['price']; ?>"
                                   min="0"
                                   step="1000"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   required>
                        </div>

                        <!-- Storage -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-hdd mr-2"></i>Storage
                            </label>
                            <select name="storage" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="64GB" <?php echo $product['storage'] == '64GB' ? 'selected' : ''; ?>>64GB</option>
                                <option value="128GB" <?php echo $product['storage'] == '128GB' ? 'selected' : ''; ?>>128GB</option>
                                <option value="256GB" <?php echo $product['storage'] == '256GB' ? 'selected' : ''; ?>>256GB</option>
                                <option value="512GB" <?php echo $product['storage'] == '512GB' ? 'selected' : ''; ?>>512GB</option>
                                <option value="1TB" <?php echo $product['storage'] == '1TB' ? 'selected' : ''; ?>>1TB</option>
                            </select>
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-palette mr-2"></i>Color
                            </label>
                            <input type="text" 
                                   name="color" 
                                   value="<?php echo htmlspecialchars($product['color']); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Condition -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-star mr-2"></i>Condition *
                            </label>
                            <select name="product_condition" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    required>
                                <option value="New" <?php echo $product['product_condition'] == 'New' ? 'selected' : ''; ?>>New</option>
                                <option value="Like New" <?php echo $product['product_condition'] == 'Like New' ? 'selected' : ''; ?>>Like New</option>
                                <option value="Good" <?php echo $product['product_condition'] == 'Good' ? 'selected' : ''; ?>>Good</option>
                                <option value="Fair" <?php echo $product['product_condition'] == 'Fair' ? 'selected' : ''; ?>>Fair</option>
                                <option value="Baru" <?php echo $product['product_condition'] == 'Baru' ? 'selected' : ''; ?>>Baru</option>
                                <option value="Bekas" <?php echo $product['product_condition'] == 'Bekas' ? 'selected' : ''; ?>>Bekas</option>
                            </select>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-boxes mr-2"></i>Stock *
                            </label>
                            <input type="number" 
                                   name="stock" 
                                   value="<?php echo $product['stock']; ?>"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   required>
                        </div>

                        <!-- Current Image -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-image mr-2"></i>Current Image
                            </label>
                            <?php if ($product['image_url']): ?>
                                <div class="relative w-32 h-32">
                                    <img src="../assets/images/<?php echo $product['image_url']; ?>" 
                                         alt="Current Product Image"
                                         class="w-full h-full object-cover rounded-lg shadow-md">
                                </div>
                                <p class="text-gray-500 text-sm mt-2 truncate"><?php echo $product['image_url']; ?></p>
                            <?php else: ?>
                                <div class="w-32 h-32 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg shadow-inner flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-gray-400 text-3xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- New Image Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-upload mr-2"></i>Upload New Image
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-500 transition">
                                <input type="file" 
                                       name="image"
                                       accept="image/*"
                                       class="w-full"
                                       onchange="previewImage(this)">
                                <div id="imagePreview" class="mt-2 hidden">
                                    <img id="preview" class="max-w-full h-auto rounded-lg max-h-32">
                                </div>
                            </div>
                            <p class="text-gray-500 text-sm mt-2">Leave empty to keep current image</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-8">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-align-left mr-2"></i>Description *
                    </label>
                    <textarea name="description" 
                              rows="6"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                              required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="manage_products.php" 
                       class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-300 transition font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" 
                            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 font-medium transition shadow-lg hover:shadow-xl">
                        <i class="fas fa-save mr-2"></i>Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>