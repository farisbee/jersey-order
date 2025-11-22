<?php
require_once 'auth.php';
requireLogin();
require_once '../db.php';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Shop Settings ---
    if (isset($_POST['update_settings'])) {
        $stmt = $pdo->prepare("UPDATE shop_settings SET shop_title = ?, shop_description = ?, admin_phone = ?, payment_instructions = ? WHERE id = 1");
        $stmt->execute([$_POST['shop_title'], $_POST['shop_description'], $_POST['admin_phone'], $_POST['payment_instructions']]);
    }
    
    // --- Fields ---
    elseif (isset($_POST['add_field'])) {
        $stmt = $pdo->prepare("INSERT INTO form_fields (label, field_name, field_type, options) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['label'], strtolower(str_replace(' ', '_', $_POST['label'])), $_POST['type'], $_POST['options']]);
    }
    elseif (isset($_POST['delete_field'])) {
        $stmt = $pdo->prepare("DELETE FROM form_fields WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    // --- Qualities ---
    elseif (isset($_POST['add_quality'])) {
        $stmt = $pdo->prepare("INSERT INTO qualities (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description']]);
    }
    elseif (isset($_POST['update_quality'])) {
        $stmt = $pdo->prepare("UPDATE qualities SET name = ?, price = ?, description = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $_POST['id']]);
    }
    elseif (isset($_POST['delete_quality'])) {
        $stmt = $pdo->prepare("DELETE FROM qualities WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    // --- Combos ---
    elseif (isset($_POST['add_combo'])) {
        $stmt = $pdo->prepare("INSERT INTO combos (name, price_adjustment, description) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description']]);
    }
    elseif (isset($_POST['update_combo'])) {
        $stmt = $pdo->prepare("UPDATE combos SET name = ?, price_adjustment = ?, description = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $_POST['id']]);
    }
    elseif (isset($_POST['delete_combo'])) {
        $stmt = $pdo->prepare("DELETE FROM combos WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    // --- Images ---
    elseif (isset($_POST['add_image'])) {
        $stmt = $pdo->prepare("INSERT INTO images (url, caption) VALUES (?, ?)");
        $stmt->execute([$_POST['url'], $_POST['caption']]);
    }
    elseif (isset($_POST['delete_image'])) {
        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    header("Location: settings.php");
    exit;
}

// Fetch Data
$settings = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
if(!$settings) $settings = ['shop_title' => 'Jersey Shop', 'shop_description' => '', 'admin_phone' => '', 'payment_instructions' => ''];

$fields = $pdo->query("SELECT * FROM form_fields ORDER BY display_order")->fetchAll();
$qualities = $pdo->query("SELECT * FROM qualities WHERE is_active = 1")->fetchAll();
$combos = $pdo->query("SELECT * FROM combos WHERE is_active = 1")->fetchAll();
$images = $pdo->query("SELECT * FROM images ORDER BY display_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">A</div>
                <h1 class="text-xl font-bold text-gray-800">Settings</h1>
            </div>
            <div class="flex gap-6 text-sm font-medium">
                <a href="index.php" class="text-gray-500 hover:text-gray-900 transition">Orders</a>
                <a href="communications.php" class="text-gray-500 hover:text-gray-900 transition">Communications</a>
                <a href="settings.php" class="text-blue-600 border-b-2 border-blue-600 pb-1">Settings</a>
                <a href="../index.php" target="_blank" class="text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    View Shop <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Shop Configuration -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                Shop Configuration
            </h2>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Shop Title</label>
                        <input type="text" name="shop_title" value="<?= htmlspecialchars($settings['shop_title']) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Shop Description</label>
                        <input type="text" name="shop_description" value="<?= htmlspecialchars($settings['shop_description']) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Admin WhatsApp</label>
                        <input type="text" name="admin_phone" value="<?= htmlspecialchars($settings['admin_phone']) ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="60123456789">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Instructions</label>
                        <textarea name="payment_instructions" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition"><?= htmlspecialchars($settings['payment_instructions']) ?></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="update_settings" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Dynamic Fields -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                Custom Fields
            </h2>
            <form method="POST" class="mb-8 space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <input type="text" name="label" placeholder="Label (e.g. Gamertag)" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                <div class="grid grid-cols-2 gap-2">
                    <select name="type" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm">
                        <option value="text">Text Input</option>
                        <option value="number">Number Input</option>
                        <option value="select">Dropdown</option>
                    </select>
                    <input type="text" name="options" placeholder="Options (comma separated)" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm">
                </div>
                <button type="submit" name="add_field" class="w-full bg-purple-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-purple-700 transition">Add Field</button>
            </form>
            <ul class="space-y-3">
                <?php foreach($fields as $f): ?>
                    <li class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100 group">
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($f['label']) ?> <span class="text-xs text-gray-400 ml-1">(<?= $f['field_type'] ?>)</span></span>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this field?');">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <button type="submit" name="delete_field" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition text-sm font-medium">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Qualities -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-green-600 rounded-full"></span>
                Jersey Qualities
            </h2>
            <form method="POST" class="mb-8 space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" placeholder="Name" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                    <input type="number" step="0.01" name="price" placeholder="Price" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                </div>
                <input type="text" name="description" placeholder="Description" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm">
                <button type="submit" name="add_quality" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-green-700 transition">Add Quality</button>
            </form>
            <ul class="space-y-4">
                <?php foreach($qualities as $q): ?>
                    <li x-data="{ edit: false }" class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div x-show="!edit" class="flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800"><?= htmlspecialchars($q['name']) ?></span>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($q['description']) ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-green-600">RM <?= $q['price'] ?></span>
                                <button @click="edit = true" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Edit</button>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this quality?');">
                                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                    <button type="submit" name="delete_quality" class="text-red-400 hover:text-red-600 text-xs font-medium">Del</button>
                                </form>
                            </div>
                        </div>
                        <!-- Edit Form -->
                        <form x-show="edit" method="POST" class="space-y-2 mt-2">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="name" value="<?= htmlspecialchars($q['name']) ?>" class="w-full border p-1 rounded text-xs">
                                <input type="number" step="0.01" name="price" value="<?= $q['price'] ?>" class="w-full border p-1 rounded text-xs">
                            </div>
                            <input type="text" name="description" value="<?= htmlspecialchars($q['description']) ?>" class="w-full border p-1 rounded text-xs">
                            <div class="flex gap-2">
                                <button type="submit" name="update_quality" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Save</button>
                                <button type="button" @click="edit = false" class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs">Cancel</button>
                            </div>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Combos -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-orange-500 rounded-full"></span>
                Combos
            </h2>
            <form method="POST" class="mb-8 space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" placeholder="Name" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                    <input type="number" step="0.01" name="price" placeholder="Extra Price" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                </div>
                <input type="text" name="description" placeholder="Description" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm">
                <button type="submit" name="add_combo" class="w-full bg-orange-500 text-white py-2 rounded-lg text-sm font-bold hover:bg-orange-600 transition">Add Combo</button>
            </form>
            <ul class="space-y-4">
                <?php foreach($combos as $c): ?>
                    <li x-data="{ edit: false }" class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div x-show="!edit" class="flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800"><?= htmlspecialchars($c['name']) ?></span>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($c['description']) ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-orange-500">+RM <?= $c['price_adjustment'] ?></span>
                                <button @click="edit = true" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Edit</button>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this combo?');">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" name="delete_combo" class="text-red-400 hover:text-red-600 text-xs font-medium">Del</button>
                                </form>
                            </div>
                        </div>
                        <!-- Edit Form -->
                        <form x-show="edit" method="POST" class="space-y-2 mt-2">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" class="w-full border p-1 rounded text-xs">
                                <input type="number" step="0.01" name="price" value="<?= $c['price_adjustment'] ?>" class="w-full border p-1 rounded text-xs">
                            </div>
                            <input type="text" name="description" value="<?= htmlspecialchars($c['description']) ?>" class="w-full border p-1 rounded text-xs">
                            <div class="flex gap-2">
                                <button type="submit" name="update_combo" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Save</button>
                                <button type="button" @click="edit = false" class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs">Cancel</button>
                            </div>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Images -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-pink-600 rounded-full"></span>
                Carousel Images
            </h2>
            <form method="POST" class="mb-8 space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <input type="url" name="url" placeholder="Image URL" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm" required>
                <input type="text" name="caption" placeholder="Caption" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm">
                <button type="submit" name="add_image" class="w-full bg-pink-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-pink-700 transition">Add Image</button>
            </form>
            <div class="grid grid-cols-3 gap-3">
                <?php foreach($images as $img): ?>
                    <div class="relative group rounded-xl overflow-hidden h-24">
                        <img src="<?= htmlspecialchars($img['url']) ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <form method="POST" onsubmit="return confirm('Delete this image?');">
                                <input type="hidden" name="id" value="<?= $img['id'] ?>">
                                <button type="submit" name="delete_image" class="text-white hover:text-red-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</body>
</html>
