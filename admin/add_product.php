<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $storage = $_POST['storage'];
    $color = $_POST['color'];
    $condition = $_POST['condition'];
    $stock = $_POST['stock'];

    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $file_type = $_FILES['image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $filename = 'iphone_' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $target_path = "../assets/images/" . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_url = $filename;
            }
        }
    }

    if (empty($image_url)) {
        $image_url = 'default_iphone.jpg'; // Default image
    }

    // Insert product
    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, image_url, storage, color, product_condition, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $price, $image_url, $storage, $color, $condition, $stock]);

        $success = '✅ Produk berhasil ditambahkan!';
        // Clear form after success
        $_POST = array();
    } catch (Exception $e) {
        $error = '❌ Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin iShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .file-upload:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        /* Mobile responsive fixes */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr !important;
            }
            .condition-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            .action-buttons {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            .action-buttons a, 
            .action-buttons button {
                width: 100% !important;
                text-align: center;
            }
        }
        @media (max-width: 640px) {
            .condition-grid {
                grid-template-columns: 1fr !important;
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
            <div class="p-4 lg:p-6">
                <a href="../index.php" class="flex items-center space-x-3 mb-6 lg:mb-8">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                        <i class="fab fa-apple text-white text-xl lg:text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-xl lg:text-2xl font-bold">iShop</span>
                        <p class="text-gray-400 text-xs">Admin Dashboard</p>
                    </div>
                </a>
                
                <div class="mb-6 lg:mb-8">
                    <div class="flex items-center space-x-3 p-3 bg-white/10 rounded-lg">
                        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm lg:text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm lg:text-base truncate"><?php echo $_SESSION['username']; ?></p>
                            <p class="text-xs text-gray-400 truncate"><?php echo $_SESSION['email']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="px-3 lg:px-4">
                <a href="index.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-tachometer-alt w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="add_product.php" class="flex items-center sidebar-link active-sidebar py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-plus-circle w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Tambah Produk</span>
                </a>
                
                <a href="upload_images.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-image w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Upload Gambar</span>
                </a>
                
                <a href="manage_users.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-users w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Kelola User</span>
                </a>
                
                <div class="border-t border-gray-700 my-3 lg:my-4"></div>
                
                <a href="../products.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-store w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Lihat Toko</span>
                </a>
                
                <a href="../index.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-sm lg:text-base">
                    <i class="fas fa-home w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Homepage</span>
                </a>
                
                <a href="../logout.php" class="flex items-center sidebar-link py-2 lg:py-3 px-3 lg:px-4 rounded-lg mb-2 text-red-300 hover:text-red-100 text-sm lg:text-base">
                    <i class="fas fa-sign-out-alt w-5 lg:w-6 mr-2 lg:mr-3 text-center"></i>
                    <span>Logout</span>
                </a>
            </nav>
            
            <div class="absolute bottom-0 w-full p-3 lg:p-4 border-t border-gray-700">
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
                <div class="px-4 sm:px-6 lg:px-8 py-3 lg:py-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 lg:gap-0">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 truncate">Tambah Produk Baru</h1>
                            <p class="text-gray-600 text-sm truncate">Tambahkan iPhone baru ke katalog</p>
                        </div>
                        <div class="flex items-center space-x-3 lg:space-x-4 mt-2 sm:mt-0">
                            <a href="index.php" class="text-gray-600 hover:text-blue-600 text-sm lg:text-base whitespace-nowrap">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>

                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Content -->
            <div class="p-4 sm:p-6 lg:p-8">
                <?php if ($error): ?>
                    <div class="mb-4 lg:mb-6 bg-red-50 border-l-4 border-red-500 p-3 lg:p-4 rounded">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 text-lg lg:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-red-700 font-medium text-sm lg:text-base"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="mb-4 lg:mb-6 bg-green-50 border-l-4 border-green-500 p-3 lg:p-4 rounded">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500 text-lg lg:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-700 font-medium text-sm lg:text-base"><?php echo $success; ?></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="index.php" class="text-green-800 font-semibold hover:underline text-sm lg:text-base">
                                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                                    </a>
                                    <a href="add_product.php" class="text-blue-600 font-semibold hover:underline text-sm lg:text-base">
                                        <i class="fas fa-plus mr-1"></i> Tambah Lagi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="max-w-full lg:max-w-6xl mx-auto">
                    <div class="bg-white rounded-xl lg:rounded-2xl shadow-lg overflow-hidden">
                        <!-- Form Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 lg:px-8 py-4 lg:py-6">
                            <div class="flex items-center">
                                <div class="bg-white p-2 lg:p-3 rounded-lg lg:rounded-xl mr-3 lg:mr-4 flex-shrink-0">
                                    <i class="fas fa-mobile-alt text-blue-600 text-lg lg:text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-lg lg:text-2xl font-bold text-white truncate">Tambah iPhone Baru</h2>
                                    <p class="text-blue-100 text-sm truncate">Lengkapi form di bawah untuk menambahkan produk</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Body -->
                        <form method="POST" enctype="multipart/form-data" class="p-4 lg:p-8">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 form-grid">
                                <!-- Left Column - Basic Info -->
                                <div class="space-y-4 lg:space-y-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                            <i class="fas fa-tag text-blue-500 mr-2"></i>Nama Produk *
                                        </label>
                                        <input type="text" name="name" required 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                               class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm lg:text-base"
                                               placeholder="Contoh: iPhone 15 Pro Max">
                                        <p class="text-xs lg:text-sm text-gray-500 mt-1">Nama produk yang jelas dan deskriptif</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                            <i class="fas fa-align-left text-blue-500 mr-2"></i>Deskripsi *
                                        </label>
                                        <textarea name="description" rows="4" required
                                                  class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm lg:text-base"
                                                  placeholder="Deskripsikan fitur dan spesifikasi produk"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        <p class="text-xs lg:text-sm text-gray-500 mt-1">Jelaskan spesifikasi dan keunggulan produk</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                                <i class="fas fa-tags text-green-500 mr-2"></i>Harga (Rp) *
                                            </label>
                                            <input type="number" name="price" required min="0" step="1000"
                                                   value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>"
                                                   class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500 text-sm lg:text-base"
                                                   placeholder="15000000">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                                <i class="fas fa-box text-purple-500 mr-2"></i>Stok *
                                            </label>
                                            <input type="number" name="stock" required min="0"
                                                   value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : '0'; ?>"
                                                   class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm lg:text-base"
                                                   placeholder="10">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column - Specifications & Image -->
                                <div class="space-y-4 lg:space-y-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                                <i class="fas fa-database text-orange-500 mr-2"></i>Storage *
                                            </label>
                                            <select name="storage" required
                                                    class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm lg:text-base">
                                                <option value="">Pilih Storage</option>
                                                <option value="64GB" <?php echo (isset($_POST['storage']) && $_POST['storage'] == '64GB') ? 'selected' : ''; ?>>64GB</option>
                                                <option value="128GB" <?php echo (isset($_POST['storage']) && $_POST['storage'] == '128GB') ? 'selected' : ''; ?>>128GB</option>
                                                <option value="256GB" <?php echo (isset($_POST['storage']) && $_POST['storage'] == '256GB') ? 'selected' : ''; ?>>256GB</option>
                                                <option value="512GB" <?php echo (isset($_POST['storage']) && $_POST['storage'] == '512GB') ? 'selected' : ''; ?>>512GB</option>
                                                <option value="1TB" <?php echo (isset($_POST['storage']) && $_POST['storage'] == '1TB') ? 'selected' : ''; ?>>1TB</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                                <i class="fas fa-palette text-pink-500 mr-2"></i>Warna *
                                            </label>
                                            <select name="color" required
                                                    class="w-full px-3 lg:px-4 py-2 lg:py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm lg:text-base">
                                                <option value="">Pilih Warna</option>
                                                <option value="Black" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Black') ? 'selected' : ''; ?>>Black</option>
                                                <option value="White" <?php echo (isset($_POST['color']) && $_POST['color'] == 'White') ? 'selected' : ''; ?>>White</option>
                                                <option value="Blue" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Blue') ? 'selected' : ''; ?>>Blue</option>
                                                <option value="Purple" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Purple') ? 'selected' : ''; ?>>Purple</option>
                                                <option value="Red" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Red') ? 'selected' : ''; ?>>Red</option>
                                                <option value="Gold" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Gold') ? 'selected' : ''; ?>>Gold</option>
                                                <option value="Sage Green" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Sage Green') ? 'selected' : ''; ?>>Sage Green</option>
                                                <option value="Lavender" <?php echo (isset($_POST['color']) && $_POST['color'] == 'Lavender') ? 'selected' : ''; ?>>Lavender</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                            <i class="fas fa-clipboard-check text-yellow-500 mr-2"></i>Kondisi *
                                        </label>
                                        <div class="grid grid-cols-3 gap-2 lg:gap-3 condition-grid">
                                            <label class="flex items-center p-3 lg:p-4 border rounded-lg cursor-pointer hover:border-blue-500 transition <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Baru') ? 'border-blue-500 bg-blue-50' : ''; ?>">
                                                <input type="radio" name="condition" value="Baru" required 
                                                       class="mr-2 lg:mr-3" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Baru') ? 'checked' : ''; ?>>
                                                <div>
                                                    <div class="font-medium text-sm lg:text-base">Baru</div>
                                                    <div class="text-xs text-gray-500">Brand new</div>
                                                </div>
                                            </label>
                                            
                                            <label class="flex items-center p-3 lg:p-4 border rounded-lg cursor-pointer hover:border-green-500 transition <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Bekas') ? 'border-green-500 bg-green-50' : ''; ?>">
                                                <input type="radio" name="condition" value="Bekas" 
                                                       class="mr-2 lg:mr-3" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Bekas') ? 'checked' : ''; ?>>
                                                <div>
                                                    <div class="font-medium text-sm lg:text-base">Bekas</div>
                                                    <div class="text-xs text-gray-500">Second hand</div>
                                                </div>
                                            </label>
                                            
                                            <label class="flex items-center p-3 lg:p-4 border rounded-lg cursor-pointer hover:border-purple-500 transition <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Refurbished') ? 'border-purple-500 bg-purple-50' : ''; ?>">
                                                <input type="radio" name="condition" value="Refurbished" 
                                                       class="mr-2 lg:mr-3" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'Refurbished') ? 'checked' : ''; ?>>
                                                <div>
                                                    <div class="font-medium text-sm lg:text-base">Refurbished</div>
                                                    <div class="text-xs text-gray-500">Like new</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm lg:text-base">
                                            <i class="fas fa-image text-indigo-500 mr-2"></i>Gambar Produk
                                        </label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg lg:rounded-xl p-4 lg:p-8 text-center file-upload hover:border-blue-400 transition">
                                            <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl lg:text-4xl mb-3 lg:mb-4"></i>
                                            <p class="text-gray-600 mb-2 text-sm lg:text-base">Drag & drop atau klik untuk upload</p>
                                            <input type="file" name="image" accept="image/*" 
                                                   class="block w-full mx-auto text-sm text-gray-500
                                                          file:mr-4 file:py-1 lg:file:py-2 file:px-3 lg:file:px-4
                                                          file:rounded-full file:border-0
                                                          file:text-xs lg:file:text-sm file:font-semibold
                                                          file:bg-blue-50 file:text-blue-700
                                                          hover:file:bg-blue-100">
                                            <p class="text-xs text-gray-500 mt-3 lg:mt-4">
                                                Format: JPG, PNG, WEBP. Maksimal 5MB.
                                            </p>
                                        </div>
                                        
                                        <!-- Preview Image (if any) -->
                                        <?php if (isset($_FILES['image']['tmp_name']) && file_exists($_FILES['image']['tmp_name'])): ?>
                                            <div class="mt-4">
                                                <p class="text-sm font-medium mb-2">Preview:</p>
                                                <img src="<?php echo $_FILES['image']['tmp_name']; ?>" 
                                                     class="w-24 h-24 lg:w-32 lg:h-32 object-cover rounded-lg border">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="mt-8 lg:mt-12 pt-6 lg:pt-8 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 action-buttons">
                                    <a href="index.php" 
                                       class="px-6 lg:px-8 py-2 lg:py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition text-center text-sm lg:text-base">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                    <button type="submit" 
                                            class="px-6 lg:px-8 py-2 lg:py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 shadow-lg transition text-center text-sm lg:text-base">
                                        <i class="fas fa-plus-circle mr-2"></i>Tambah Produk
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Quick Tips -->
                    <div class="mt-6 lg:mt-8 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-xl p-4 lg:p-6">
                        <h3 class="font-bold text-blue-800 mb-3 lg:mb-4 text-sm lg:text-base">
                            <i class="fas fa-lightbulb mr-2"></i>Tips Menambahkan Produk
                        </h3>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 lg:gap-3 text-xs lg:text-sm text-blue-700">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2 flex-shrink-0"></i>
                                Gunakan gambar dengan background putih untuk hasil terbaik
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2 flex-shrink-0"></i>
                                Tulis deskripsi yang detail dan informatif
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2 flex-shrink-0"></i>
                                Harga harus realistis dan kompetitif
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2 flex-shrink-0"></i>
                                Update stok secara berkala
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
        
        // File upload preview
        const fileInput = document.querySelector('input[type="file"]');
        const fileUploadArea = document.querySelector('.file-upload');
        
        if (fileInput && fileUploadArea) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove existing preview
                        const existingPreview = document.querySelector('.image-preview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }
                        
                        // Create new preview
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'mt-4 image-preview';
                        previewDiv.innerHTML = `
                            <p class="text-sm font-medium mb-2">Preview:</p>
                            <img src="${e.target.result}" 
                                 class="w-24 h-24 lg:w-32 lg:h-32 object-cover rounded-lg border shadow-sm">
                        `;
                        
                        fileUploadArea.parentNode.appendChild(previewDiv);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Drag and drop
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('border-blue-500', 'bg-blue-50');
            });
            
            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('border-blue-500', 'bg-blue-50');
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        }
        
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