<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

$search = $_GET['search'] ?? '';
$color = $_GET['color'] ?? '';
$condition = $_GET['condition'] ?? ''; // Tetap pakai 'condition' untuk GET parameter

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($color)) {
    $sql .= " AND color = ?";
    $params[] = $color;
}

if (!empty($condition)) {
    $sql .= " AND product_condition = ?"; // Tapi di query pakai product_condition
    $params[] = $condition;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique colors and conditions for filter
$colorStmt = $pdo->query("SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color != '' ORDER BY color");
$colors = $colorStmt->fetchAll(PDO::FETCH_COLUMN);

$conditionStmt = $pdo->query("SELECT DISTINCT product_condition FROM products WHERE product_condition IS NOT NULL AND product_condition != '' ORDER BY product_condition");
$conditions = $conditionStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include 'includes/header.php'; ?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Di bagian filter sidebar -->
    <div class="lg:w-1/4">
        <!-- FORM TAMBAHAN: Form untuk filter -->
        <form method="GET" action="products.php" id="filterForm">
            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6 sticky top-24">
                <h3 class="text-xl font-bold mb-6 pb-4 border-b">Filter Products</h3>

                <!-- Search -->
                <div class="mb-6">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Search iPhones..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Color Filter -->
                <div class="mb-6">
                    <h4 class="font-semibold mb-3 text-gray-700">Colors</h4>
                    <div class="space-y-2">
                        <?php if (!empty($colors)): ?>
                            <?php foreach ($colors as $c): ?>
                                <?php if (!empty($c)): ?>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="color" value="<?php echo htmlspecialchars($c); ?>"
                                            class="hidden"
                                            <?php echo $color == $c ? 'checked' : ''; ?>
                                            onchange="document.getElementById('filterForm').submit()">
                                        <div class="w-6 h-6 rounded-full border mr-3 <?php echo $color == $c ? 'ring-2 ring-blue-500' : ''; ?>"
                                            style="background-color: <?php echo strtolower($c); ?>"></div>
                                        <span class="text-gray-700"><?php echo htmlspecialchars($c); ?></span>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="color" value=""
                                class="hidden"
                                <?php echo empty($color) ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit()">
                            <div class="w-6 h-6 rounded-full border mr-3 bg-gray-200 <?php echo empty($color) ? 'ring-2 ring-blue-500' : ''; ?>"></div>
                            <span class="text-gray-700">All Colors</span>
                        </label>
                    </div>
                </div>

                <!-- Condition Filter -->
                <div class="mb-6">
                    <h4 class="font-semibold mb-3 text-gray-700">Condition</h4>
                    <div class="space-y-2">
                        <?php if (!empty($conditions)): ?>
                            <?php foreach ($conditions as $cond): ?>
                                <?php if (!empty($cond)): ?>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="condition" value="<?php echo htmlspecialchars($cond); ?>"
                                            class="hidden"
                                            <?php echo $condition == $cond ? 'checked' : ''; ?>
                                            onchange="document.getElementById('filterForm').submit()">
                                        <div class="w-4 h-4 border rounded-full mr-3 flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full <?php echo $condition == $cond ? 'bg-blue-500' : 'bg-transparent'; ?>"></div>
                                        </div>
                                        <span class="text-gray-700"><?php echo htmlspecialchars($cond); ?></span>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm">No conditions available</p>
                        <?php endif; ?>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="condition" value=""
                                class="hidden"
                                <?php echo empty($condition) ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit()">
                            <div class="w-4 h-4 border rounded-full mr-3 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full <?php echo empty($condition) ? 'bg-blue-500' : 'bg-transparent'; ?>"></div>
                            </div>
                            <span class="text-gray-700">All Conditions</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition">
                    Apply Filters
                </button>
                <a href="products.php" class="block w-full text-center mt-3 text-gray-600 hover:text-blue-600 transition">
                    Clear All Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="lg:w-3/4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">iPhone Collection</h1>
                <p class="text-gray-600"><?php echo count($products); ?> products found</p>
            </div>
            
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-16 bg-white rounded-2xl shadow-lg">
                <i class="fas fa-search fa-4x text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold mb-3">No Products Found</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Try adjusting your search or filter to find what you're looking for.
                </p>
                <a href="products.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 font-semibold">
                    Reset Filters
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <?php
                    // PERBAIKAN: Check if product is in favorites
                    $isFavorite = false;
                    if (isLoggedIn() && $_SESSION['role'] == 'user') {
                        $favStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
                        $favStmt->execute([$_SESSION['user_id'], $product['id']]);
                        $isFavorite = $favStmt->fetch();
                    }
                    ?>
                    <div class="group bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                        <!-- Product image -->
                        <div class="relative overflow-hidden h-76">
                            <?php if (!empty($product['image_url']) && file_exists("assets/images/" . $product['image_url'])): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($product['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-gray-400 text-6xl"></i>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['stock'] == 0): ?>
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                    <span class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold">
                                        Out of Stock
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Badges -->
                            <div class="absolute top-4 left-4">
                                <?php if ($product['product_condition'] == 'Baru'): ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        NEW
                                    </span>
                                <?php endif; ?>
                                <?php if ($product['product_condition'] == 'Bekas'): ?>
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        USED
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Favorite button -->
                            <?php if (isLoggedIn() && $_SESSION['role'] == 'user'): ?>
                                <form action="favorites.php" method="POST" class="absolute top-4 right-4">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="toggle_favorite"
                                        class="w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition"
                                        onclick="event.stopPropagation()">
                                        <i class="fas fa-heart <?php echo $isFavorite ? 'text-red-500' : 'text-gray-400'; ?> hover:text-red-500"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Product info -->
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <span class="text-2xl font-bold text-blue-600">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </span>
                            </div>

                            <p class="text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                            </p>

                            <!-- Specs -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Storage</div>
                                    <div class="font-semibold"><?php echo htmlspecialchars($product['storage']); ?></div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="text-gray-500 text-sm">Color</div>
                                    <div class="font-semibold"><?php echo htmlspecialchars($product['color']); ?></div>
                                </div>
                            </div>

                            <!-- Stock & Condition -->
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-sm font-medium px-3 py-1 rounded-full <?php echo $product['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Sold Out'; ?>
                                </span>
                                <span class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($product['product_condition']); ?>
                                </span>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex space-x-3">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-center py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 font-medium transition">
                                    View Details
                                </a>
                                <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                                    
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>
function sortProducts(value) {
    console.log('Sort by:', value);
}

// Validasi warna untuk background color
document.addEventListener('DOMContentLoaded', function() {
    const colorDivs = document.querySelectorAll('[style*="background-color:"]');
    colorDivs.forEach(div => {
        const color = div.style.backgroundColor;
        // Jika warna tidak valid, beri warna default
        if (!color || color === 'undefined') {
            div.style.backgroundColor = '#6b7280'; // Gray color
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>