<?php
require_once 'auth.php';
requireLogin();
require_once '../db.php';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_content'])) {
        $stmt = $pdo->prepare("UPDATE shop_settings SET 
            image_disclaimer = ?, 
            delivery_disclaimer = ?,
            success_message = ?,
            whatsapp_message_template = ?
            WHERE id = 1");
        $stmt->execute([
            $_POST['image_disclaimer'],
            $_POST['delivery_disclaimer'],
            $_POST['success_message'],
            $_POST['whatsapp_message_template']
        ]);
        $success = "Content updated successfully!";
    }
    
    // Update size chart
    elseif (isset($_POST['update_size_chart'])) {
        $stmt = $pdo->prepare("UPDATE shop_settings SET size_chart_image = ? WHERE id = 1");
        $stmt->execute([$_POST['size_chart_path']]);
        $success = "Size chart updated!";
    }
    
    // Update logo
    elseif (isset($_POST['update_logo'])) {
        $stmt = $pdo->prepare("UPDATE shop_settings SET shop_logo = ? WHERE id = 1");
        $stmt->execute([$_POST['logo_path']]);
        $success = "Logo updated!";
    }
}

// Fetch current settings
$settings = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
if (!$settings) {
    $settings = [
        'image_disclaimer' => 'Product images are for illustration purposes only. Actual product may vary.',
        'delivery_disclaimer' => 'Estimated delivery: 1 month after order closes',
        'success_message' => 'Thank you for your order! We\'ll contact you shortly.',
        'whatsapp_message_template' => 'Hi, I just placed Order #{order_id}.\nName: {name}\nTotal: {total}\nHere is my payment receipt.',
        'size_chart_image' => '',
        'shop_logo' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="contentManager()">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">A</div>
                <h1 class="text-xl font-bold text-gray-800">Content Management</h1>
            </div>
            <div class="flex gap-6 text-sm font-medium">
                <a href="index.php" class="text-gray-500 hover:text-gray-900 transition">Orders</a>
                <a href="communications.php" class="text-gray-500 hover:text-gray-900 transition">Communications</a>
                <a href="settings.php" class="text-gray-500 hover:text-gray-900 transition">Settings</a>
                <a href="content.php" class="text-blue-600 border-b-2 border-blue-600 pb-1">Content</a>
                <a href="../index.php" target="_blank" class="text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    View Shop <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2 mb-8">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Shop Branding -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                    Shop Logo
                </h2>
                
                <div class="space-y-4">
                    <?php if (!empty($settings['shop_logo'])): ?>
                        <div class="border rounded-xl p-4 bg-gray-50">
                            <img src="../<?= htmlspecialchars($settings['shop_logo']) ?>" alt="Current Logo" class="max-h-32 mx-auto">
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload New Logo</label>
                        <input type="file" @change="uploadFile($event, 'logo')" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        <p class="text-xs text-gray-500 mt-2">Recommended: PNG with transparent background, max 5MB</p>
                    </div>
                    
                    <div x-show="uploading" class="text-sm text-gray-600">
                        <svg class="animate-spin inline h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Uploading...
                    </div>
                </div>
            </div>

            <!-- Size Chart -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                    Size Chart
                </h2>
                
                <div class="space-y-4">
                    <?php if (!empty($settings['size_chart_image'])): ?>
                        <div class="border rounded-xl p-4 bg-gray-50">
                            <img src="../<?= htmlspecialchars($settings['size_chart_image']) ?>" alt="Size Chart" class="w-full">
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Size Chart Image</label>
                        <input type="file" @change="uploadFile($event, 'size_chart')" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-2">Clear image showing size measurements, max 5MB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disclaimers & Messages -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                Disclaimers & Customer Messages
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Image Disclaimer</label>
                        <textarea name="image_disclaimer" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition"><?= htmlspecialchars($settings['image_disclaimer']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Shown below product carousel</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Delivery Disclaimer</label>
                        <textarea name="delivery_disclaimer" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition"><?= htmlspecialchars($settings['delivery_disclaimer']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Shown above "Place Order" button</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Success Message</label>
                    <textarea name="success_message" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition"><?= htmlspecialchars($settings['success_message']) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Shown in success modal after order placement. Variables: {name}, {order_id}, {total}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp Message Template</label>
                    <textarea name="whatsapp_message_template" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition font-mono text-sm"><?= htmlspecialchars($settings['whatsapp_message_template']) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Pre-filled message for WhatsApp. Variables: {order_id}, {name}, {total}</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_content" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">Save All Content</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function contentManager() {
            return {
                uploading: false,
                
                async uploadFile(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    this.uploading = true;
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('upload_type', type);
                    
                    try {
                        const response = await fetch('upload-handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Submit form to update database
                            const updateForm = document.createElement('form');
                            updateForm.method = 'POST';
                            updateForm.innerHTML = `
                                <input type="hidden" name="${type === 'size_chart' ? 'update_size_chart' : 'update_logo'}" value="1">
                                <input type="hidden" name="${type}_path" value="${result.path}">
                            `;
                            document.body.appendChild(updateForm);
                            updateForm.submit();
                        } else {
                            alert('Upload failed: ' + result.message);
                        }
                    } catch (error) {
                        alert('Upload error: ' + error.message);
                    } finally {
                        this.uploading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
