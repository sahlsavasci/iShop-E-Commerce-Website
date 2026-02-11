<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(stock) as total FROM products");
$stats['total_stock'] = $stmt->fetchColumn();

// Get recent products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
$recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - iShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .sidebar-link {
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
        }
        .active-sidebar {
            background: rgba(255,255,255,0.15);
            border-left: 4px solid #3b82f6;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }
        /* Mobile responsive fixes */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(1, 1fr) !important;
            }
            .content-grid {
                grid-template-columns: 1fr !important;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        @media (max-width: 1024px) {
            .lg\:col-span-2 {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 right-4 z-50">
        <button id="mobile-sidebar-toggle" class="bg-gray-800 text-white p-3 rounded-lg shadow-lg">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Admin Layout -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed lg:relative z-40 w-64 h-full lg:h-auto bg-gradient-to-b from-gray-900 to-black text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
            <div class="p-6">
                <a href="../index.php" class="flex items-center space-x-3 mb-8">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                        <i class="fab fa-apple text-white text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold">iShop</span>
                        <p class="text-gray-400 text-xs">Admin Dashboard</p>
                    </div>
                </a>
                
                <div class="mb-8">
                    <div class="flex items-center space-x-3 p-3 bg-white/10 rounded-lg">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate"><?php echo $_SESSION['username']; ?></p>
                            <p class="text-xs text-gray-400 truncate"><?php echo $_SESSION['email']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="px-4">
                <a href="index.php" class="flex items-center sidebar-link active-sidebar py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-tachometer-alt w-6 mr-3 text-center"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="add_product.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-plus-circle w-6 mr-3 text-center"></i>
                    <span>Tambah Produk</span>
                </a>
                
                <a href="upload_images.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-image w-6 mr-3 text-center"></i>
                    <span>Upload Gambar</span>
                </a>
                
                <a href="manage_users.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-users w-6 mr-3 text-center"></i>
                    <span>Kelola User</span>
                </a>
                
                <div class="border-t border-gray-700 my-4"></div>
                
                <a href="../products.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-store w-6 mr-3 text-center"></i>
                    <span>Lihat Toko</span>
                </a>
                
                <a href="../index.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2">
                    <i class="fas fa-home w-6 mr-3 text-center"></i>
                    <span>Homepage</span>
                </a>
                
                <a href="../logout.php" class="flex items-center sidebar-link py-3 px-4 rounded-lg mb-2 text-red-300 hover:text-red-100">
                    <i class="fas fa-sign-out-alt w-6 mr-3 text-center"></i>
                    <span>Logout</span>
                </a>
            </nav>
            
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-700">
                <p class="text-xs text-gray-500 text-center">
                    iShop Admin Panel v1.0<br>
                    © 2024 All rights reserved
                </p>
            </div>
        </div>
        
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>
        
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">Dashboard Admin</h1>
                            <p class="text-gray-600 text-sm sm:text-base truncate">Selamat datang, <?php echo $_SESSION['username']; ?>!</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="p-4 sm:p-6 lg:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8 stats-grid">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg card-hover">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-blue-100 text-sm font-medium">Total Produk</p>
                                <p class="text-2xl sm:text-3xl font-bold mt-2 truncate"><?php echo $stats['total_products']; ?></p>
                                <p class="text-blue-200 text-xs sm:text-sm mt-2 truncate">
                                    <i class="fas fa-arrow-up mr-1"></i> 12% dari bulan lalu
                                </p>
                            </div>
                            <div class="bg-white/20 p-2 sm:p-4 rounded-lg sm:rounded-xl ml-4 flex-shrink-0">
                                <i class="fas fa-mobile-alt text-xl sm:text-3xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg card-hover">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-green-100 text-sm font-medium">Total User</p>
                                <p class="text-2xl sm:text-3xl font-bold mt-2 truncate"><?php echo $stats['total_users']; ?></p>
                                <p class="text-green-200 text-xs sm:text-sm mt-2 truncate">
                                    <i class="fas fa-user-plus mr-1"></i> 5 user baru
                                </p>
                            </div>
                            <div class="bg-white/20 p-2 sm:p-4 rounded-lg sm:rounded-xl ml-4 flex-shrink-0">
                                <i class="fas fa-users text-xl sm:text-3xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg card-hover">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-purple-100 text-sm font-medium">Total Stok</p>
                                <p class="text-2xl sm:text-3xl font-bold mt-2 truncate"><?php echo $stats['total_stock'] ?? 0; ?></p>
                                <p class="text-purple-200 text-xs sm:text-sm mt-2 truncate">
                                    <i class="fas fa-boxes mr-1"></i> Semua produk
                                </p>
                            </div>
                            <div class="bg-white/20 p-2 sm:p-4 rounded-lg sm:rounded-xl ml-4 flex-shrink-0">
                                <i class="fas fa-boxes text-xl sm:text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Products & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 content-grid">
                    <!-- Recent Products -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Produk Terbaru</h2>
                                <div class="flex space-x-4">
                                    <a href="add_product.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium whitespace-nowrap">
                                        <i class="fas fa-plus mr-1"></i> Tambah Baru
                                    </a>
                                    <a href="manage_products.php" class="text-gray-600 hover:text-gray-800 text-sm font-medium whitespace-nowrap">
                                        <i class="fas fa-list mr-1"></i> Lihat Semua
                                    </a>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-max">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-3 text-gray-700 font-medium text-sm">Produk</th>
                                                <th class="text-left py-3 text-gray-700 font-medium text-sm">Harga</th>
                                                <th class="text-left py-3 text-gray-700 font-medium text-sm">Stok</th>
                                                <th class="text-left py-3 text-gray-700 font-medium text-sm">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_products as $product): ?>
                                                <tr class="border-b hover:bg-gray-50 transition">
                                                    <td class="py-3 sm:py-4">
                                                        <div class="flex items-center">
                                                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-lg mr-3 sm:mr-4 overflow-hidden flex-shrink-0">
                                                                <?php if ($product['image_url'] && file_exists("../assets/images/" . $product['image_url'])): ?>
                                                                    <img src="../assets/images/<?php echo $product['image_url']; ?>" 
                                                                         alt="<?php echo $product['name']; ?>"
                                                                         class="w-full h-full object-cover">
                                                                <?php else: ?>
                                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-mobile-alt text-sm sm:text-base"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <div class="font-semibold text-gray-800 text-sm sm:text-base truncate"><?php echo $product['name']; ?></div>
                                                                <div class="text-xs sm:text-sm text-gray-500 truncate"><?php echo $product['color']; ?> • <?php echo $product['storage']; ?></div>
                                                                <div class="text-xs text-gray-500 mt-1">
                                                                    <span class="px-2 py-1 rounded-full <?php echo $product['product_condition'] == 'Baru' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                                        <?php echo $product['product_condition']; ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 sm:py-4">
                                                        <span class="font-bold text-blue-600 text-sm sm:text-base">
                                                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-3 sm:py-4">
                                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                                              <?php echo $product['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                            <?php echo $product['stock']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-3 sm:py-4">
                                                        <div class="flex space-x-1 sm:space-x-2">
                                                            <a href="../product_detail.php?id=<?php echo $product['id']; ?>" 
                                                               class="text-blue-600 hover:text-blue-800 p-1 sm:p-2 rounded hover:bg-blue-50"
                                                               title="View">
                                                                <i class="fas fa-eye text-sm"></i>
                                                            </a>
                                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                               class="text-green-600 hover:text-green-800 p-1 sm:p-2 rounded hover:bg-green-50"
                                                               title="Edit">
                                                                <i class="fas fa-edit text-sm"></i>
                                                            </a>
                                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                                               class="text-red-600 hover:text-red-800 p-1 sm:p-2 rounded hover:bg-red-50"
                                                               title="Delete"
                                                               onclick="return confirm('Yakin hapus <?php echo addslashes($product['name']); ?>?')">
                                                                <i class="fas fa-trash text-sm"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if (empty($recent_products)): ?>
                                <div class="text-center py-8 sm:py-12">
                                    <i class="fas fa-box-open text-3xl sm:text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-base sm:text-lg font-semibold text-gray-600">Belum ada produk</h3>
                                    <p class="text-gray-500 mt-2 text-sm">Tambahkan produk pertama Anda</p>
                                    <a href="add_product.php" class="inline-block mt-4 bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 text-sm sm:text-base">
                                        <i class="fas fa-plus mr-2"></i>Tambah Produk
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions & Stats -->
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 sm:mb-6">Quick Actions</h2>
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <a href="add_product.php" 
                                   class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center hover:from-blue-100 hover:to-blue-200 transition">
                                    <i class="fas fa-plus-circle text-xl sm:text-2xl text-blue-600 mb-1 sm:mb-2"></i>
                                    <p class="font-medium text-blue-800 text-sm sm:text-base">Tambah Produk</p>
                                </a>
                                
                                <a href="upload_images.php" 
                                   class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center hover:from-green-100 hover:to-green-200 transition">
                                    <i class="fas fa-image text-xl sm:text-2xl text-green-600 mb-1 sm:mb-2"></i>
                                    <p class="font-medium text-green-800 text-sm sm:text-base">Upload Gambar</p>
                                </a>
                                
                                <a href="manage_users.php" 
                                   class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center hover:from-purple-100 hover:to-purple-200 transition">
                                    <i class="fas fa-users text-xl sm:text-2xl text-purple-600 mb-1 sm:mb-2"></i>
                                    <p class="font-medium text-purple-800 text-sm sm:text-base">Kelola User</p>
                                </a>
                                
                                <a href="../products.php" 
                                   class="bg-gradient-to-r from-orange-50 to-orange-100 border border-orange-200 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center hover:from-orange-100 hover:to-orange-200 transition">
                                    <i class="fas fa-store text-xl sm:text-2xl text-orange-600 mb-1 sm:mb-2"></i>
                                    <p class="font-medium text-orange-800 text-sm sm:text-base">Lihat Toko</p>
                                </a>
                            </div>
                        </div>
                        
                        <!-- System Status -->
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 sm:mb-6">System Status</h2>
                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full mr-2 sm:mr-3"></div>
                                        <span class="text-gray-700 text-sm sm:text-base">Database</span>
                                    </div>
                                    <span class="text-green-600 font-medium text-sm sm:text-base">Online</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full mr-2 sm:mr-3"></div>
                                        <span class="text-gray-700 text-sm sm:text-base">Storage</span>
                                    </div>
                                    <span class="text-green-600 font-medium text-sm sm:text-base">45% Used</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full mr-2 sm:mr-3"></div>
                                        <span class="text-gray-700 text-sm sm:text-base">Last Backup</span>
                                    </div>
                                    <span class="text-gray-600 text-sm sm:text-base">Today, 02:00</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-blue-500 rounded-full mr-2 sm:mr-3"></div>
                                        <span class="text-gray-700 text-sm sm:text-base">Active Sessions</span>
                                    </div>
                                    <span class="text-blue-600 font-medium text-sm sm:text-base">3 Users</span>
                                </div>
                            </div>
                            
                            <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t">
                                <a href="../logout.php" 
                                   class="flex items-center justify-center w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-2 sm:py-3 rounded-lg hover:from-red-600 hover:to-red-700 font-medium text-sm sm:text-base">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Script -->
    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('mobile-sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
            
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
        
        // Auto update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        // Update time every minute
        setInterval(updateTime, 60000);
        updateTime(); // Initial call
        
        // Close sidebar when clicking on links (mobile)
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                }
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>
</body>
</html>