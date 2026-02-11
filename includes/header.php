<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iShop - Premium iPhone Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        /* Dropdown animation */
        .dropdown-enter {
            opacity: 0;
            transform: translateY(-10px);
        }
        .dropdown-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 200ms, transform 200ms;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <?php
                // Tentukan URL home yang tepat berdasarkan lokasi file
                $home_url = 'index.php';
                if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false) {
                    $home_url = '../index.php';
                }
                ?>
                <a href="<?php echo $home_url; ?>" class="flex items-center space-x-2">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                        <i class="fab fa-apple text-white text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            iShop
                        </span>
                        <p class="text-xs text-gray-500">Premium iPhone Store</p>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <?php
                    // Tentukan URL yang tepat berdasarkan lokasi file
                    $base_url = '';
                    if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false) {
                        $base_url = '../';
                    }
                    ?>
                    <a href="<?php echo $base_url; ?>index.php" class="nav-link text-gray-700 hover:text-blue-600 font-medium">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="<?php echo $base_url; ?>products.php" class="nav-link text-gray-700 hover:text-blue-600 font-medium">
                        <i class="fas fa-mobile-alt mr-2"></i>Products
                    </a>

                    <?php if (isLoggedIn()): ?>
                        <?php
                        // Hitung favorites hanya untuk user biasa, bukan admin
                        $fav_count = 0;
                        if (isset($_SESSION['user_id']) && !isAdmin()) {
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $fav_count = $stmt->fetchColumn();
                        }
                        ?>
                        
                        <!-- Hanya tampilkan favorites untuk user biasa, bukan admin -->
                        <?php if (!isAdmin()): ?>
                            <a href="<?php echo $base_url; ?>favorites.php" class="nav-link text-gray-700 hover:text-blue-600 font-medium relative">
                                <i class="fas fa-heart mr-2"></i>Favorites
                                <?php if ($fav_count > 0): ?>
                                    <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-1">
                                        <?php echo $fav_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <?php if (isAdmin()): ?>
                            <a href="<?php echo $base_url; ?>admin/index.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 font-medium shadow-md transition-all duration-300 hover:scale-105">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>

                        <!-- User Dropdown -->
                        <div class="relative group" id="user-dropdown">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 focus:outline-none transition-all duration-200" id="dropdown-toggle">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white shadow-md">
                                    <?php if (isAdmin()): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="font-medium"><?php echo $_SESSION['username']; ?></span>
                                <i class="fas fa-chevron-down text-sm transition-transform duration-200" id="dropdown-icon"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-3 hidden group-hover:block hover:block border border-gray-100" id="dropdown-menu">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800"><?php echo $_SESSION['username']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $_SESSION['email']; ?></p>
                                    <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full <?php echo isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo isAdmin() ? 'Administrator' : 'Customer'; ?>
                                    </span>
                                </div>
                                
                                <div class="py-2">
                                    <!-- Hanya tampilkan favorites di dropdown untuk user biasa -->
                                    <?php if (!isAdmin()): ?>
                                        <a href="<?php echo $base_url; ?>favorites.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                            <i class="fas fa-heart mr-3 text-red-500"></i>
                                            <span>My Favorites</span>
                                            <?php if ($fav_count > 0): ?>
                                                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                                                    <?php echo $fav_count; ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (isAdmin()): ?>
                                        <a href="<?php echo $base_url; ?>admin/index.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition">
                                            <i class="fas fa-cog mr-3 text-purple-500"></i>
                                            <span>Admin Dashboard</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="border-t border-gray-100 pt-2">
                                    <a href="<?php echo $base_url; ?>logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-2 rounded-lg hover:from-green-600 hover:to-green-700 font-medium shadow-md transition-all duration-300 hover:scale-105">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700 focus:outline-none transition-transform duration-200 hover:scale-110">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden py-4 border-t bg-white rounded-lg mt-2 shadow-lg">
                <div class="flex flex-col space-y-3 px-4">
                    <a href="<?php echo $base_url; ?>index.php" class="flex items-center text-gray-700 hover:text-blue-600 py-3 px-3 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-home mr-4 w-6 text-center text-blue-500"></i>Home
                    </a>
                    <a href="<?php echo $base_url; ?>products.php" class="flex items-center text-gray-700 hover:text-blue-600 py-3 px-3 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-mobile-alt mr-4 w-6 text-center text-blue-500"></i>Products
                    </a>

                    <?php if (isLoggedIn()): ?>
                        <!-- Hanya tampilkan favorites untuk user biasa di mobile -->
                        <?php if (!isAdmin()): ?>
                            <a href="<?php echo $base_url; ?>favorites.php" class="flex items-center text-gray-700 hover:text-blue-600 py-3 px-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-heart mr-4 w-6 text-center text-red-500"></i>Favorites
                                <?php if ($fav_count > 0): ?>
                                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                                        <?php echo $fav_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <?php if (isAdmin()): ?>
                            <a href="<?php echo $base_url; ?>admin/index.php" class="flex items-center text-gray-700 hover:text-purple-600 py-3 px-3 rounded-lg hover:bg-purple-50 transition">
                                <i class="fas fa-cog mr-4 w-6 text-center text-purple-500"></i>Admin Panel
                            </a>
                        <?php endif; ?>

                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white shadow-md mr-3">
                                    <?php if (isAdmin()): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $_SESSION['username']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo isAdmin() ? 'Administrator' : 'Customer'; ?></p>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <!-- Hanya tampilkan favorites di dropdown mobile untuk user biasa -->
                                <?php if (!isAdmin()): ?>
                                    <a href="<?php echo $base_url; ?>favorites.php" class="flex items-center text-gray-600 hover:text-blue-600 py-2 px-3 rounded hover:bg-gray-100">
                                        <i class="fas fa-heart mr-3 w-6 text-center text-red-500"></i>
                                        <span>My Favorites</span>
                                        <?php if ($fav_count > 0): ?>
                                            <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1 py-1">
                                                <?php echo $fav_count; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo $base_url; ?>logout.php" class="flex items-center text-red-600 hover:text-red-800 py-2 px-3 rounded hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-3 w-6 text-center"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="flex items-center text-gray-700 hover:text-blue-600 py-3 px-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-sign-in-alt mr-4 w-6 text-center text-blue-500"></i>Login
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="flex items-center text-white bg-gradient-to-r from-green-500 to-green-600 py-3 px-3 rounded-lg hover:from-green-600 hover:to-green-700 transition mt-2">
                            <i class="fas fa-user-plus mr-4 w-6 text-center"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle dengan animasi
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const icon = this.querySelector('i');
            
            menu.classList.toggle('hidden');
            
            // Animasi icon hamburger menjadi X
            if (menu.classList.contains('hidden')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });

        // Dropdown dengan click untuk mobile
        const dropdownToggle = document.getElementById('dropdown-toggle');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const dropdownIcon = document.getElementById('dropdown-icon');
        
        if (dropdownToggle) {
            // Untuk touch devices (mobile)
            dropdownToggle.addEventListener('click', function(e) {
                if (window.innerWidth < 768) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('hidden');
                    dropdownIcon.classList.toggle('rotate-180');
                }
            });
            
            // Close dropdown ketika klik di luar
            document.addEventListener('click', function(e) {
                if (window.innerWidth < 768) {
                    const dropdown = document.getElementById('user-dropdown');
                    if (!dropdown.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                        dropdownIcon.classList.remove('rotate-180');
                    }
                }
            });
        }

        // Close mobile menu ketika klik link
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('mobile-menu').classList.add('hidden');
                document.querySelector('#mobile-menu-button i').classList.remove('fa-times');
                document.querySelector('#mobile-menu-button i').classList.add('fa-bars');
            });
        });

        // Tambahkan class untuk rotasi dropdown icon
        const style = document.createElement('style');
        style.textContent = `
            .rotate-180 {
                transform: rotate(180deg);
            }
            #dropdown-menu {
                animation: dropdownFade 0.2s ease-out;
            }
            @keyframes dropdownFade {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>

    <main class="container mx-auto px-4 py-8">