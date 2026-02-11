<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $product_ids = $_POST['product_ids'] ?? [];
    
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        switch ($_POST['bulk_action']) {
            case 'delete':
                // Get images to delete
                $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id IN ($placeholders)");
                $stmt->execute($product_ids);
                $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Delete images
                foreach ($images as $image) {
                    if ($image) {
                        $imagePath = '../assets/images/' . $image;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                }
                
                // Delete from favorites
                $pdo->prepare("DELETE FROM favorites WHERE product_id IN ($placeholders)")->execute($product_ids);
                
                // Delete products
                $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                if ($stmt->execute($product_ids)) {
                    $_SESSION['success'] = count($product_ids) . " product(s) deleted successfully!";
                }
                break;
                
            case 'out_of_stock':
                $stmt = $pdo->prepare("UPDATE products SET stock = 0 WHERE id IN ($placeholders)");
                if ($stmt->execute($product_ids)) {
                    $_SESSION['success'] = count($product_ids) . " product(s) marked as out of stock!";
                }
                break;
                
            case 'in_stock':
                $stmt = $pdo->prepare("UPDATE products SET stock = 10 WHERE id IN ($placeholders)");
                if ($stmt->execute($product_ids)) {
                    $_SESSION['success'] = count($product_ids) . " product(s) stock updated!";
                }
                break;
        }
    }
    header('Location: manage_products.php');
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(stock) as total_stock,
        SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM products
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 truncate">Manage Products</h1>
                    <p class="text-gray-600 text-sm sm:text-base truncate">Manage all products in your store</p>
                </div>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <a href="add_product.php" 
                       class="inline-flex items-center bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 font-medium transition whitespace-nowrap text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </a>
                    <a href="index.php" 
                       class="inline-flex items-center bg-gray-200 text-gray-700 px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-gray-300 transition font-medium whitespace-nowrap text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 sm:mb-6 bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-box text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-blue-100 text-xs sm:text-sm font-medium">Total Products</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mt-1 truncate"><?php echo $stats['total_products']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-warehouse text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-green-100 text-xs sm:text-sm font-medium">Total Stock</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mt-1 truncate"><?php echo $stats['total_stock'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-orange-100 text-xs sm:text-sm font-medium">Out of Stock</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mt-1 truncate"><?php echo $stats['out_of_stock']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <form method="POST" id="bulkForm" class="mb-4 sm:mb-6">
            <div class="bg-white rounded-xl shadow p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="selectAll" class="ml-2 text-gray-700 font-medium text-sm sm:text-base">Select All</label>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                        <span class="text-gray-600 text-sm sm:text-base text-center sm:text-left" id="selectedCount">0 selected</span>
                        
                        <div class="flex flex-col sm:flex-row gap-2">
                            <select name="bulk_action" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                                <option value="out_of_stock">Mark as Out of Stock</option>
                                <option value="in_stock">Mark as In Stock</option>
                            </select>
                            
                            <button type="submit" 
                                    class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 font-medium transition text-sm sm:text-base">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Products Table -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-10 sm:w-12">
                                    <span class="sr-only">Select</span>
                                </th>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider min-w-[150px]">
                                    Product
                                </th>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Price
                                </th>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Condition
                                </th>
                                <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="px-4 sm:px-6 py-8 sm:py-12 text-center">
                                        <i class="fas fa-box-open text-3xl sm:text-4xl text-gray-300 mb-3 sm:mb-4"></i>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-600">No products found</h3>
                                        <p class="text-gray-500 mt-1 sm:mt-2 text-sm">Add your first product to get started</p>
                                        <a href="add_product.php" class="inline-block mt-3 sm:mt-4 bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 text-sm sm:text-base">
                                            <i class="fas fa-plus mr-2"></i>Add Product
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <input type="checkbox" 
                                                   name="product_ids[]" 
                                                   value="<?php echo $product['id']; ?>"
                                                   class="product-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 bg-gray-100 rounded-lg overflow-hidden">
                                                    <?php if ($product['image_url']): ?>
                                                        <img src="../assets/images/<?php echo $product['image_url']; ?>" 
                                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                             class="h-full w-full object-cover">
                                                    <?php else: ?>
                                                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                                                            <i class="fas fa-mobile-alt text-sm sm:text-base"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-3 sm:ml-4 min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 truncate">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </div>
                                                    <div class="text-xs sm:text-sm text-gray-500 truncate">
                                                        <?php echo $product['color']; ?> • <?php echo $product['storage']; ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 hidden sm:block">
                                                        <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <span class="font-bold text-blue-600 text-sm sm:text-base">
                                                Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <span class="px-2 py-1 rounded-full text-xs sm:text-sm font-medium
                                                  <?php echo $product['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <span class="px-2 py-1 rounded-full text-xs sm:text-sm font-medium
                                                  <?php echo $product['product_condition'] == 'New' || $product['product_condition'] == 'Baru' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $product['product_condition']; ?>
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                            <div class="flex space-x-1 sm:space-x-2">
                                                <a href="../product_detail.php?id=<?php echo $product['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-800 p-1 sm:p-2 rounded hover:bg-blue-50"
                                                   title="View" target="_blank">
                                                    <i class="fas fa-eye text-xs sm:text-sm"></i>
                                                </a>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="text-green-600 hover:text-green-800 p-1 sm:p-2 rounded hover:bg-green-50"
                                                   title="Edit">
                                                    <i class="fas fa-edit text-xs sm:text-sm"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="text-red-600 hover:text-red-800 p-1 sm:p-2 rounded hover:bg-red-50"
                                                   title="Delete"
                                                   onclick="return confirm('Delete <?php echo addslashes($product['name']); ?>?')">
                                                    <i class="fas fa-trash text-xs sm:text-sm"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination Info -->
        <div class="mt-4 sm:mt-6 text-center text-gray-600 text-sm sm:text-base">
            Showing <?php echo count($products); ?> products
        </div>
    </div>
</div>

<script>
// Bulk selection
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
            updateSelectedCount();
        });
    }

    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const selectedCountElement = document.getElementById('selectedCount');
        if (selectedCountElement) {
            selectedCountElement.textContent = checkboxes.length + ' selected';
        }
    }

    // Attach event listeners to all checkboxes
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Initial count update
    updateSelectedCount();

    // Confirm bulk delete
    const bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const action = this.bulk_action.value;
            const checkboxes = document.querySelectorAll('.product-checkbox:checked');
            
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one product.');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete ${checkboxes.length} product(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                }
            }
        });
    }

    // Make table rows clickable for better mobile experience
    document.querySelectorAll('tbody tr').forEach(row => {
        if (window.innerWidth < 768) {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on checkbox or action buttons
                if (!e.target.closest('input[type="checkbox"]') && 
                    !e.target.closest('a') && 
                    !e.target.closest('button')) {
                    
                    const checkbox = this.querySelector('.product-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        }
    });
});

// Handle window resize
window.addEventListener('resize', function() {
    // Update row click behavior based on screen size
    document.querySelectorAll('tbody tr').forEach(row => {
        if (window.innerWidth < 768) {
            row.style.cursor = 'pointer';
        } else {
            row.style.cursor = 'default';
            row.onclick = null;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>