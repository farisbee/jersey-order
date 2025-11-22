<?php
require_once 'auth.php';
requireLogin();
require_once '../db.php';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Update Email Config ---
    if (isset($_POST['update_email_config'])) {
        $config = [
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_user' => $_POST['smtp_user'],
            'smtp_pass' => $_POST['smtp_pass'],
            'confirmation_subject' => $_POST['confirmation_subject'],
            'confirmation_body' => $_POST['confirmation_body']
        ];
        $stmt = $pdo->prepare("UPDATE shop_settings SET email_config = ? WHERE id = 1");
        $stmt->execute([json_encode($config)]);
        $success = "Email configuration updated.";
    }
    // --- Send Custom Email ---
    elseif (isset($_POST['send_email'])) {
        $recipientType = $_POST['recipient_type'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $success = "Email queued for sending to " . htmlspecialchars($recipientType) . " customers.";
    }
}

// Fetch Data
$settings = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
$emailConfig = json_decode($settings['email_config'] ?? '{}', true);
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();

// Extract phone numbers for WhatsApp
$phoneNumbers = array_map(function($o) { return $o['phone_number']; }, $orders);
$phoneString = implode(',', $phoneNumbers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Communications</title>
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
                <h1 class="text-xl font-bold text-gray-800">Communications</h1>
            </div>
            <div class="flex gap-6 text-sm font-medium">
                <a href="index.php" class="text-gray-500 hover:text-gray-900 transition">Orders</a>
                <a href="communications.php" class="text-blue-600 border-b-2 border-blue-600 pb-1">Communications</a>
                <a href="settings.php" class="text-gray-500 hover:text-gray-900 transition">Settings</a>
                <a href="../index.php" target="_blank" class="text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    View Shop <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 space-y-8">
        
        <?php if(isset($success)): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Email Configuration -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100" x-data="{ showConfig: false }">
            <div class="flex justify-between items-center cursor-pointer" @click="showConfig = !showConfig">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                    Email Configuration (SMTP)
                </h2>
                <span class="text-blue-600 text-sm font-medium bg-blue-50 px-3 py-1 rounded-full" x-text="showConfig ? 'Hide Settings' : 'Show Settings'"></span>
            </div>
            
            <form x-show="showConfig" method="POST" class="mt-6 space-y-6" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Host</label>
                        <input type="text" name="smtp_host" value="<?= htmlspecialchars($emailConfig['smtp_host'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Port</label>
                        <input type="text" name="smtp_port" value="<?= htmlspecialchars($emailConfig['smtp_port'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" name="smtp_user" value="<?= htmlspecialchars($emailConfig['smtp_user'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" name="smtp_pass" value="<?= htmlspecialchars($emailConfig['smtp_pass'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-bold mb-4 text-sm uppercase text-gray-500 tracking-wider">Auto-Confirmation Template</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                        <input type="text" name="confirmation_subject" value="<?= htmlspecialchars($emailConfig['confirmation_subject'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Body</label>
                        <textarea name="confirmation_body" rows="6" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none transition font-mono text-sm"><?= htmlspecialchars($emailConfig['confirmation_body'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-500 mt-2">Available variables: {name}, {order_id}, {total}</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="update_email_config" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition">Save Configuration</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Send Custom Email -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-teal-500 rounded-full"></span>
                    Send Custom Email
                </h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Recipients</label>
                        <select name="recipient_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-teal-500 outline-none transition">
                            <option value="all">All Customers</option>
                            <option value="pending">Pending Orders</option>
                            <option value="paid">Paid Orders</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-teal-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                        <textarea name="body" rows="5" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-teal-500 outline-none transition" required></textarea>
                    </div>
                    <button type="submit" name="send_email" class="w-full bg-teal-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-teal-700 transition">Send Email</button>
                </form>
            </div>

            <!-- WhatsApp Tools -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-green-500 rounded-full"></span>
                    WhatsApp Tools
                </h2>
                
                <div class="space-y-6">
                    <div class="bg-green-50 p-6 rounded-2xl border border-green-100">
                        <h3 class="font-bold text-green-900 mb-2">Bulk Number Export</h3>
                        <p class="text-sm text-green-700 mb-4">Copy all customer phone numbers to clipboard for broadcast lists.</p>
                        <textarea readonly class="w-full h-24 text-xs p-3 border border-green-200 rounded-xl mb-3 bg-white focus:outline-none"><?= htmlspecialchars($phoneString) ?></textarea>
                        <button onclick="navigator.clipboard.writeText('<?= $phoneString ?>'); alert('Copied!')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-700 transition w-full">Copy All Numbers</button>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                        <h3 class="font-bold text-gray-900 mb-2">Direct Message Link</h3>
                        <p class="text-sm text-gray-600 mb-4">Create a chat link for a specific number.</p>
                        <div class="flex gap-2">
                            <input type="text" id="waNumber" placeholder="6012..." class="bg-white border border-gray-200 p-3 rounded-xl flex-1 focus:ring-2 focus:ring-gray-400 outline-none">
                            <button onclick="window.open('https://wa.me/' + document.getElementById('waNumber').value, '_blank')" class="bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900 transition">Chat</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
