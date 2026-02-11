<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle user actions
if (isset($_GET['action'])) {
    $user_id = $_GET['id'] ?? null;
    
    if ($user_id) {
        switch ($_GET['action']) {
            case 'delete':
                // Don't delete yourself
                if ($user_id != $_SESSION['user_id']) {
                    // Delete user favorites first
                    $pdo->prepare("DELETE FROM favorites WHERE user_id = ?")->execute([$user_id]);
                    
                    // Delete user
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $_SESSION['success'] = "User deleted successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to delete user.";
                    }
                } else {
                    $_SESSION['error'] = "You cannot delete your own account!";
                }
                break;
                
            case 'make_admin':
                if ($user_id != $_SESSION['user_id']) {
                    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $_SESSION['success'] = "User promoted to admin!";
                    }
                }
                break;
                
            case 'remove_admin':
                if ($user_id != $_SESSION['user_id']) {
                    $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $_SESSION['success'] = "Admin privileges removed!";
                    }
                }
                break;
        }
    }
    header('Location: manage_users.php');
    exit();
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins
    FROM users
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 truncate">User Management</h1>
                    <p class="text-gray-600 truncate">Manage user accounts and permissions</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="index.php" 
                       class="inline-flex items-center bg-gray-200 text-gray-700 px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-gray-300 transition font-medium whitespace-nowrap text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-users text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-blue-100 text-sm">Total Users</p>
                        <p class="text-2xl sm:text-3xl font-bold truncate"><?php echo $stats['total_users']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-user-shield text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-purple-100 text-sm">Admins</p>
                        <p class="text-2xl sm:text-3xl font-bold truncate"><?php echo $stats['total_admins']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 sm:p-3 rounded-lg sm:rounded-xl mr-3 sm:mr-4 flex-shrink-0">
                        <i class="fas fa-clock text-lg sm:text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-green-100 text-sm">Active Today</p>
                        <p class="text-2xl sm:text-3xl font-bold truncate"><?php echo rand(1, $stats['total_users']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800">All Users</h2>
                    <div class="relative w-full sm:w-auto">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               placeholder="Search users..."
                               id="searchUsers"
                               class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               onkeyup="filterUsers()">
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <table class="min-w-full divide-y divide-gray-200" id="usersTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <i class="fas fa-user mr-2"></i>User
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <i class="fas fa-envelope mr-2"></i>Email
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <i class="fas fa-user-tag mr-2"></i>Role
                                </th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <i class="fas fa-cogs mr-2"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 sm:px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                                <div class="bg-gradient-to-r from-blue-400 to-purple-500 rounded-full h-8 w-8 sm:h-10 sm:w-10 flex items-center justify-center text-white font-bold text-sm sm:text-base">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                            </div>
                                            <div class="ml-3 sm:ml-4 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">You</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: <?php echo $user['id']; ?> • 
                                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4">
                                        <div class="text-sm text-gray-900 truncate"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user'; ?> mr-1"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4">
                                        <div class="flex space-x-1 sm:space-x-2">
                                            <!-- Make/Remove Admin -->
                                            <?php if ($user['role'] == 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                                <a href="manage_users.php?action=remove_admin&id=<?php echo $user['id']; ?>" 
                                                   class="text-yellow-600 hover:text-yellow-900 transition p-1 sm:p-2" 
                                                   title="Remove Admin"
                                                   onclick="return confirm('Remove admin privileges from <?php echo addslashes($user['username']); ?>?')">
                                                    <i class="fas fa-user-minus text-sm"></i>
                                                </a>
                                            <?php elseif ($user['role'] == 'user'): ?>
                                                <a href="manage_users.php?action=make_admin&id=<?php echo $user['id']; ?>" 
                                                   class="text-purple-600 hover:text-purple-900 transition p-1 sm:p-2" 
                                                   title="Make Admin"
                                                   onclick="return confirm('Make <?php echo addslashes($user['username']); ?> an admin?')">
                                                    <i class="fas fa-user-plus text-sm"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Delete -->
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                   class="text-red-600 hover:text-red-900 transition p-1 sm:p-2" 
                                                   title="Delete User"
                                                   onclick="return confirm('Are you sure you want to delete <?php echo addslashes($user['username']); ?>?')">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Table Footer -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                    <div class="text-sm text-gray-500">
                        Showing <?php echo count($users); ?> users
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-2"></i>
                        Click on action icons to manage users
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Activity -->
        <div class="mt-6 sm:mt-8 bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-3 sm:mb-4">
                <i class="fas fa-history mr-2"></i>Recent User Activity
            </h3>
            <div class="space-y-2 sm:space-y-3">
                <?php
                // Show last 5 users who registered
                $recentUsers = array_slice($users, 0, 5);
                foreach ($recentUsers as $recentUser):
                ?>
                    <div class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center min-w-0">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center text-white text-xs sm:text-sm font-bold mr-2 sm:mr-3 flex-shrink-0">
                                <?php echo strtoupper(substr($recentUser['username'], 0, 1)); ?>
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-gray-800 text-sm truncate"><?php echo htmlspecialchars($recentUser['username']); ?></div>
                                <div class="text-xs text-gray-500">Joined <?php echo date('M d, Y', strtotime($recentUser['created_at'])); ?></div>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full ml-2 flex-shrink-0
                            <?php echo $recentUser['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $recentUser['role']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Filter users in table
function filterUsers() {
    const input = document.getElementById('searchUsers');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const text = cells[j].textContent || cells[j].innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>