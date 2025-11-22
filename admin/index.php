<?php
require_once 'auth.php';
requireLogin();
require_once '../db.php';

$orders = $pdo->query("
    SELECT o.*, q.name as quality_name, c.name as combo_name 
    FROM orders o 
    LEFT JOIN qualities q ON o.quality_id = q.id 
    LEFT JOIN combos c ON o.combo_id = c.id 
    ORDER BY o.created_at DESC
")->fetchAll();

$settings = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
$adminPhone = $settings['admin_phone'] ?? '60123456789';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">A</div>
                <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
            </div>
            <div class="flex gap-6 text-sm font-medium">
                <a href="index.php" class="text-blue-600 border-b-2 border-blue-600 pb-1">Orders</a>
                <a href="communications.php" class="text-gray-500 hover:text-gray-900 transition">Communications</a>
                <a href="settings.php" class="text-gray-500 hover:text-gray-900 transition">Settings</a>
                <a href="content.php" class="text-gray-500 hover:text-gray-900 transition">Content</a>
                <a href="../index.php" target="_blank" class="text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    View Shop <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-sm font-medium uppercase">Total Orders</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-2"><?= count($orders) ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-sm font-medium uppercase">Pending Payment</p>
                <p class="text-3xl font-extrabold text-orange-500 mt-2">
                    <?= count(array_filter($orders, fn($o) => $o['status'] === 'pending')) ?>
                </p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-sm font-medium uppercase">Total Revenue</p>
                <p class="text-3xl font-extrabold text-green-600 mt-2">
                    RM <?= number_format(array_sum(array_column($orders, 'total_price')), 2) ?>
                </p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Recent Orders</h2>
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><?= count($orders) ?> records</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Customer</th>
                            <th class="p-4 font-semibold">Details</th>
                            <th class="p-4 font-semibold">Custom Fields</th>
                            <th class="p-4 font-semibold">Notes</th>
                            <th class="p-4 font-semibold">Total</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        <?php foreach($orders as $order): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-mono text-gray-500">#<?= $order['id'] ?></td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($order['phone_number']) ?></div>
                                    <?php if(!empty($order['customer_email'])): ?>
                                        <div class="text-xs text-gray-400"><?= htmlspecialchars($order['customer_email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium"><?= htmlspecialchars($order['quality_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($order['combo_name']) ?></div>
                                    <div class="text-xs mt-1 bg-gray-100 inline-block px-1 rounded">
                                        Size: <?= $order['jersey_size'] ?> | No: <?= $order['jersey_number'] ?> | Qty: <?= $order['quantity'] ?>
                                    </div>
                                </td>
                                <td class="p-4 text-xs">
                                    <?php 
                                        $custom = json_decode($order['custom_data'] ?? '{}', true);
                                        foreach($custom as $k => $v) {
                                            echo "<div class='mb-1'><span class='text-gray-500'>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</span> " . htmlspecialchars($v) . "</div>";
                                        }
                                    ?>
                                </td>
                                <td class="p-4 text-xs italic text-gray-500 max-w-xs truncate">
                                    <?= htmlspecialchars($order['customer_notes']) ?>
                                </td>
                                <td class="p-4 font-bold text-gray-900">
                                    RM <?= $order['total_price'] ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold 
                                        <?= $order['status'] === 'paid' ? 'bg-green-100 text-green-700' : 
                                           ($order['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <a href="https://wa.me/<?= $order['phone_number'] ?>?text=Hi%20<?= urlencode($order['customer_name']) ?>,%20regarding%20your%20order%20%23<?= $order['id'] ?>..." 
                                       target="_blank" 
                                       class="text-green-600 hover:text-green-800 font-medium text-xs flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.667-.272-.099-.47-.149-.669-.149-.198 0-.42.001-.643.001-.223 0-.586.085-.892.41-.307.325-1.177 1.151-1.177 2.807 0 1.657 1.207 3.258 1.375 3.482.168.224 2.376 3.628 5.757 5.088 2.288.988 2.752.791 3.247.742.495-.049 1.584-.648 1.807-1.274.223-.625.223-1.161.156-1.274z"/></svg>
                                        WhatsApp
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
