<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $uploadDir = '../assets/images/';
        $uploadedFiles = [];
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                $uploadFile = $uploadDir . $fileName;
                
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['images']['tmp_name'][$i]);
                
                if (in_array($fileType, $allowedTypes)) {
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadFile)) {
                        $uploadedFiles[] = $fileName;
                    }
                }
            }
        }
        
        if (!empty($uploadedFiles)) {
            $_SESSION['success'] = count($uploadedFiles) . " image(s) uploaded successfully!";
        } else {
            $_SESSION['error'] = "No images were uploaded.";
        }
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $imageName = $_GET['delete'];
    $imagePath = '../assets/images/' . $imageName;
    
    if (file_exists($imagePath)) {
        if (unlink($imagePath)) {
            $_SESSION['success'] = "Image deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete image.";
        }
    }
    header('Location: upload_images.php');
    exit();
}

// Get all uploaded images
$uploadDir = '../assets/images/';
$images = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $images[] = $file;
        }
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
                    <h1 class="text-3xl font-bold text-gray-800">Image Manager</h1>
                    <p class="text-gray-600">Upload and manage product images</p>
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

        <!-- Upload Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Images
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition">
                        <i class="fas fa-images text-gray-400 text-5xl mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Drop images here or click to upload</h3>
                        <p class="text-gray-500 mb-4">Supports JPG, PNG, GIF, WEBP (Max 5MB each)</p>
                        
                        <input type="file" 
                               name="images[]" 
                               multiple
                               accept="image/*"
                               id="imageInput"
                               class="hidden"
                               onchange="previewImages(this)">
                        
                        <label for="imageInput" 
                               class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 font-medium transition cursor-pointer">
                            <i class="fas fa-folder-open mr-2"></i>Select Images
                        </label>
                        
                        <div id="imagePreviews" class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 hidden">
                            <!-- Image previews will appear here -->
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                name="upload"
                                class="bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-lg hover:from-green-700 hover:to-green-800 font-medium transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-upload mr-2"></i>Upload Images
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gallery Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-images mr-2"></i>Image Gallery
                </h2>
                
                <?php if (empty($images)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-image text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No images found</h3>
                        <p class="text-gray-500">Upload your first image to get started</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <?php foreach ($images as $image): ?>
                            <div class="group relative bg-gray-100 rounded-lg overflow-hidden hover:shadow-lg transition">
                                <div class="aspect-square">
                                    <img src="../assets/images/<?php echo $image; ?>" 
                                         alt="Uploaded Image"
                                         class="w-full h-full object-cover">
                                </div>
                                
                                <!-- Image info -->
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3 opacity-0 group-hover:opacity-100 transition">
                                    <p class="text-white text-xs truncate"><?php echo $image; ?></p>
                                </div>
                                
                                <!-- Delete button -->
                                <div class="absolute top-2 right-2">
                                    <a href="upload_images.php?delete=<?php echo urlencode($image); ?>" 
                                       class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-red-600"
                                       onclick="return confirm('Delete this image?')">
                                        <i class="fas fa-trash text-xs"></i>
                                    </a>
                                </div>
                                
                                <!-- Copy URL button -->
                                <div class="absolute top-2 left-2">
                                    <button onclick="copyImageUrl('<?php echo $image; ?>')" 
                                            class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-blue-600">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Stats -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-gray-600">
                                <i class="fas fa-database mr-2"></i>
                                Total: <?php echo count($images); ?> images
                            </div>
                            <div class="text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                Click on copy icon to get image URL
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
function previewImages(input) {
    const previewContainer = document.getElementById('imagePreviews');
    previewContainer.innerHTML = '';
    previewContainer.classList.remove('hidden');
    
    const files = input.files;
    for (let i = 0; i < Math.min(files.length, 8); i++) { // Limit previews
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative bg-gray-100 rounded-lg overflow-hidden';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-32 object-cover">
                <div class="absolute top-1 right-1">
                    <span class="bg-black/50 text-white text-xs px-2 py-1 rounded">
                        ${(file.size / 1024 / 1024).toFixed(2)} MB
                    </span>
                </div>
            `;
            previewContainer.appendChild(div);
        }
        
        reader.readAsDataURL(file);
    }
}

// Copy image URL to clipboard
function copyImageUrl(imageName) {
    const url = 'assets/images/' + imageName;
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.textContent = 'Image URL copied to clipboard!';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    });
}
</script>

<?php include '../includes/footer.php'; ?>