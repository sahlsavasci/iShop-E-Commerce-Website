<?php
require_once 'config/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch featured products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch new arrivals
$stmt_new = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 3");
$new_arrivals = $stmt_new->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="relative overflow-hidden rounded-2xl mb-16">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2080&q=80"
            alt="iPhone Collection"
            class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/40"></div>
    </div>

    <div class="relative z-10 py-24 px-8 text-center">
        <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 animate__animated animate__fadeInDown">
            Discover Your Next iPhone
        </h1>
        <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto animate__animated animate__fadeInUp">
            Experience innovation, elegance, and cutting-edge technology with our premium collection of iPhones.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center animate__animated animate__fadeInUp">
            <a href="products.php"
                class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full text-lg font-semibold hover:from-blue-700 hover:to-purple-700 shadow-lg transform hover:-translate-y-1 transition duration-300">
                <i class="fas fa-shopping-bag mr-2"></i>Shop Now
            </a>
            <a href="#featured"
                class="bg-white/20 backdrop-blur-sm text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/30 border border-white/30 transition duration-300">
                <i class="fas fa-eye mr-2"></i>Browse Collection
            </a>
        </div>
    </div>
</section>

<!-- Features -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="text-blue-600 mb-4">
            <i class="fas fa-shield-alt text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold mb-3">Authentic Products</h3>
        <p class="text-gray-600">100% genuine iPhones with official warranty and certification.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="text-green-600 mb-4">
            <i class="fas fa-truck text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold mb-3">Free Shipping</h3>
        <p class="text-gray-600">Free delivery for all orders above Rp 5,000,000 across Indonesia.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="text-purple-600 mb-4">
            <i class="fas fa-headset text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold mb-3">24/7 Support</h3>
        <p class="text-gray-600">Dedicated customer service ready to assist you anytime.</p>
    </div>
</div>

<!-- New Arrivals -->
<section class="mb-16">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">New Arrivals</h2>
            <p class="text-gray-600">Latest iPhone models just arrived</p>
        </div>
        <a href="products.php" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
            View All <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($new_arrivals as $index => $product): ?>
            <div class="group bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                <div class="relative overflow-hidden h-74">
                    <?php if ($product['image_url']): ?>
                        <img src="assets/images/<?php echo $product['image_url']; ?>"
                            alt="<?php echo $product['name']; ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-gray-400 text-6xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-4 right-4">
                        <span class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            NEW
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">
                            <?php echo $product['name']; ?>
                        </h3>
                        <span class="text-lg font-bold text-blue-600">
                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <p class="text-gray-600 mb-4 line-clamp-2">
                        <?php echo substr($product['description'], 0, 80); ?>...
                    </p>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                            <?php echo $product['storage']; ?>
                        </span>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                            <?php echo $product['color']; ?>
                        </span>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                            <?php echo $product['product_condition']; ?>
                        </span>
                    </div>

                    <div class="flex space-x-3">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-center py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 font-medium transition">
                            View Details
                        </a>
                        <?php if (isLoggedIn() && $_SESSION['role'] == 'user'): ?>
                            <?php
                            $favStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
                            $favStmt->execute([$_SESSION['user_id'], $product['id']]);
                            $isFavorite = $favStmt->fetch();
                            ?>
                            <form action="favorites.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="toggle_favorite"
                                    class="w-12 bg-gray-100 text-<?php echo $isFavorite ? 'red-500' : 'gray-400'; ?> hover:text-red-500 rounded-lg flex items-center justify-center hover:bg-gray-200 transition">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" class="mb-16">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Featured Products</h2>
            <p class="text-gray-600">Curated selection of premium iPhones</p>
        </div>
        <a href="products.php" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
            View All <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($products as $product): ?>
            <div class="group bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                <div class="relative overflow-hidden h-76">
                    <?php if ($product['image_url']): ?>
                        <img src="assets/images/<?php echo $product['image_url']; ?>"
                            alt="<?php echo $product['name']; ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-gray-400 text-6xl"></i>
                        </div>
                    <?php endif; ?>

                    <?php if ($product['stock'] == 0): ?>
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                            <span class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold">
                                Out of Stock
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">
                            <?php echo $product['name']; ?>
                        </h3>
                        <span class="text-lg font-bold text-blue-600">
                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <p class="text-gray-600 mb-4 line-clamp-2">
                        <?php echo substr($product['description'], 0, 80); ?>...
                    </p>

                    <div class="flex justify-between items-center mb-4">
                        <div class="flex space-x-2">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
                                <?php echo $product['storage']; ?>
                            </span>
                            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                                <?php echo $product['color']; ?>
                            </span>
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                            <span class="text-green-600 text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> In Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                        class="block w-full bg-gradient-to-r from-gray-800 to-black text-white text-center py-3 rounded-lg hover:from-gray-900 hover:to-black font-medium transition">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-center mb-16">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Get Your New iPhone?</h2>
        <p class="text-blue-100 mb-8">
            Join thousands of satisfied customers who found their perfect iPhone at iShop.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="products.php"
                class="bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                Browse Collection
            </a>
            <a href="#"
                class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white/10 transition">
                Contact Sales
            </a>
        </div>
    </div>
</section>

<!-- Brands -->
<div class="mb-16">
    <h3 class="text-center text-gray-500 text-sm uppercase tracking-wider mb-8">Trusted By</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fab fa-apple text-gray-400 text-4xl"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fas fa-shield-alt text-gray-400 text-3xl"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fas fa-award text-gray-400 text-3xl"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fas fa-star text-gray-400 text-3xl"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fas fa-check-circle text-gray-400 text-3xl"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex items-center justify-center">
            <i class="fas fa-thumbs-up text-gray-400 text-3xl"></i>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>