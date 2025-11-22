<?php
require_once 'db.php';

// Fetch Shop Settings
$settings = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
if(!$settings) {
    $settings = [
        'shop_title' => 'Jersey Shop',
        'shop_description' => 'Customize your kit like a pro.',
        'admin_phone' => '60123456789', 
        'payment_instructions' => '',
        'email_config' => '{}'
    ];
}
$emailConfig = json_decode($settings['email_config'] ?? '{}', true);

// Fetch Dynamic Data
$qualities = $pdo->query("SELECT * FROM qualities WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$combos = $pdo->query("SELECT * FROM combos WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
$images = $pdo->query("SELECT * FROM images ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$formFields = $pdo->query("SELECT * FROM form_fields ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Calculate Total (Server-side validation)
        $qualityPrice = 0;
        foreach ($qualities as $q) {
            if ($q['id'] == $input['quality_id']) $qualityPrice = $q['price'];
        }
        
        $comboPrice = 0;
        foreach ($combos as $c) {
            if ($c['id'] == $input['combo_id']) $comboPrice = $c['price_adjustment'];
        }
        
        $total = ($qualityPrice + $comboPrice) * $input['quantity'];

        // Insert Order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, phone_number, quality_id, combo_id, jersey_number, jersey_size, quantity, total_price, custom_data, customer_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $customData = [];
        foreach ($formFields as $field) {
            $fieldName = $field['field_name'];
            if (isset($input[$fieldName])) {
                $customData[$fieldName] = $input[$fieldName];
            }
        }

        $stmt->execute([
            $input['name'],
            $input['email'],
            $input['phone'],
            $input['quality_id'],
            $input['combo_id'],
            $input['number'],
            $input['size'],
            $input['quantity'],
            $total,
            json_encode($customData),
            $input['notes'] ?? ''
        ]);

        $orderId = $pdo->lastInsertId();

        // --- Send Confirmation Email (Simulated) ---
        if (!empty($input['email'])) {
            $subject = $emailConfig['confirmation_subject'] ?? "Order Confirmation";
            $body = $emailConfig['confirmation_body'] ?? "Thank you for your order!";
            $body = str_replace('{name}', $input['name'], $body);
            $body = str_replace('{order_id}', $orderId, $body);
            $body = str_replace('{total}', number_format($total, 2), $body);
            // mail($input['email'], $subject, $body); 
        }

        echo json_encode(['success' => true, 'order_id' => $orderId, 'total' => $total]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['shop_title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800">

    <div class="min-h-screen flex flex-col lg:flex-row" x-data="shopApp()">
        
        <!-- Left Side: Visuals (Sticky on Desktop) -->
        <div class="h-96 lg:h-screen lg:w-1/2 lg:sticky lg:top-0 bg-gray-900 relative overflow-hidden">
            <!-- Carousel -->
            <div class="absolute inset-0">
                <template x-for="(img, index) in images" :key="index">
                    <div x-show="activeSlide === index" 
                         x-transition:enter="transition transform duration-700 ease-out"
                         x-transition:enter-start="scale-110 opacity-0"
                         x-transition:enter-end="scale-100 opacity-100"
                         x-transition:leave="transition transform duration-700 ease-in"
                         x-transition:leave-start="scale-100 opacity-100"
                         x-transition:leave-end="scale-90 opacity-0"
                         class="absolute inset-0 w-full h-full">
                        <img :src="img.url" class="w-full h-full object-cover opacity-60">
                    </div>
                </template>
            </div>
            
            <!-- Content Overlay -->
            <div class="absolute inset-0 flex flex-col justify-end p-8 lg:p-16 bg-gradient-to-t from-black/90 via-black/30 to-transparent">
                <h1 class="text-4xl lg:text-6xl font-extrabold text-white mb-2 tracking-tight leading-tight">
                    <?= htmlspecialchars($settings['shop_title']) ?>
                </h1>
                <p class="text-lg text-gray-300 max-w-md"><?= htmlspecialchars($settings['shop_description']) ?></p>
                
                <!-- Carousel Dots -->
                <div class="flex space-x-2 mt-6">
                    <template x-for="(img, index) in images" :key="index">
                        <button @click="activeSlide = index" 
                                :class="{'w-8 bg-white': activeSlide === index, 'w-2 bg-white/40': activeSlide !== index}" 
                                class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right Side: Order Form -->
        <div class="lg:w-1/2 bg-white">
            <form @submit.prevent="submitOrder" class="max-w-xl mx-auto p-6 lg:p-12 space-y-10 pb-32">
                
                <!-- Section 1: Personal Info -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span class="bg-black text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                        Your Details
                    </h2>
                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
                            <input type="text" x-model="form.name" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none" placeholder="John Doe" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Email Address</label>
                                <input type="email" x-model="form.email" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none" placeholder="john@example.com" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">WhatsApp Number</label>
                                <input type="tel" x-model="form.phone" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none" placeholder="60123456789" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Jersey Config -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span class="bg-black text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                        Build Your Kit
                    </h2>
                    
                    <!-- Quality Selection (Cards) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-3">Select Version</label>
                        <div class="grid grid-cols-1 gap-3">
                            <template x-for="q in qualities" :key="q.id">
                                <div @click="form.quality_id = q.id" 
                                     :class="{'ring-2 ring-black bg-gray-50': form.quality_id == q.id, 'border border-gray-200 hover:border-gray-400': form.quality_id != q.id}"
                                     class="cursor-pointer rounded-xl p-4 flex justify-between items-center transition-all">
                                    <div>
                                        <span class="font-bold text-lg block" x-text="q.name"></span>
                                        <span class="text-sm text-gray-500" x-text="q.description"></span>
                                    </div>
                                    <span class="font-bold text-lg" x-text="'RM ' + q.price"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Combo Selection (Cards) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-3">Add-ons</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="c in combos" :key="c.id">
                                <div @click="form.combo_id = c.id"
                                     :class="{'ring-2 ring-black bg-gray-50': form.combo_id == c.id, 'border border-gray-200 hover:border-gray-400': form.combo_id != c.id}"
                                     class="cursor-pointer rounded-xl p-4 transition-all h-full flex flex-col justify-between">
                                    <div>
                                        <span class="font-bold block" x-text="c.name"></span>
                                        <span class="text-xs text-gray-500 mt-1 block" x-text="c.description"></span>
                                    </div>
                                    <div class="mt-3 text-right font-bold text-sm" x-text="parseFloat(c.price_adjustment) > 0 ? '+RM ' + c.price_adjustment : 'Included'"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Size & Number -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Size</label>
                            <div class="relative">
                                <select x-model="form.size" class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none">
                                    <option>S</option><option>M</option><option>L</option><option>XL</option><option>XXL</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Jersey Number</label>
                            <input type="number" x-model="form.number" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none" placeholder="10">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Customization -->
                <div class="space-y-6" x-show="formFields.length > 0">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span class="bg-black text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                        Customization
                    </h2>
                    <template x-for="field in formFields" :key="field.id">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1" x-text="field.label"></label>
                            
                            <template x-if="field.field_type === 'text'">
                                <input type="text" x-model="form[field.field_name]" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none">
                            </template>

                            <template x-if="field.field_type === 'number'">
                                <input type="number" x-model="form[field.field_name]" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none">
                            </template>

                            <template x-if="field.field_type === 'select'">
                                <div class="relative">
                                    <select x-model="form[field.field_name]" class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none">
                                        <option value="">Select...</option>
                                        <template x-for="opt in field.options.split(',')" :key="opt">
                                            <option :value="opt.trim()" x-text="opt.trim()"></option>
                                        </template>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Special Notes</label>
                        <textarea x-model="form.notes" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-black focus:border-transparent transition outline-none" placeholder="Any special requests?"></textarea>
                    </div>
                </div>

            </form>

            <!-- Sticky Footer -->
            <div class="fixed bottom-0 left-0 w-full lg:w-1/2 lg:left-auto lg:right-0 bg-white/80 backdrop-blur-md border-t border-gray-200 p-4 shadow-2xl z-20">
                <div class="max-w-xl mx-auto flex justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button type="button" @click="if(form.quantity > 1) form.quantity--" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white shadow-sm transition">-</button>
                            <span class="w-8 text-center font-bold" x-text="form.quantity"></span>
                            <button type="button" @click="form.quantity++" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-white shadow-sm transition">+</button>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                            <p class="text-2xl font-extrabold text-black" x-text="'RM ' + calculateTotal().toFixed(2)"></p>
                        </div>
                    </div>
                    <button @click="submitOrder" :disabled="loading" class="flex-1 bg-black text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:bg-gray-800 hover:scale-[1.02] active:scale-95 transition disabled:opacity-50 disabled:hover:scale-100">
                        <span x-show="!loading">Place Order</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccess" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-transition.opacity>
            <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform transition-all scale-100" @click.away="showSuccess = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Order Confirmed!</h3>
                <p class="text-gray-500 mb-6">Thanks <span x-text="form.name" class="font-bold text-gray-800"></span>! We've received your order.</p>
                
                <div class="bg-gray-50 p-5 rounded-2xl text-left text-sm text-gray-700 mb-6 whitespace-pre-wrap border border-gray-100" x-text="paymentInstructions"></div>

                <a :href="whatsappLink" target="_blank" class="block w-full bg-[#25D366] text-white font-bold py-4 rounded-xl hover:bg-[#128C7E] hover:shadow-lg transition flex items-center justify-center gap-3">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.683-2.031-9.667-.272-.099-.47-.149-.669-.149-.198 0-.42.001-.643.001-.223 0-.586.085-.892.41-.307.325-1.177 1.151-1.177 2.807 0 1.657 1.207 3.258 1.375 3.482.168.224 2.376 3.628 5.757 5.088 2.288.988 2.752.791 3.247.742.495-.049 1.584-.648 1.807-1.274.223-.625.223-1.161.156-1.274z"/></svg>
                    Send Receipt via WhatsApp
                </a>
                <button @click="showSuccess = false" class="mt-4 text-gray-400 text-sm hover:text-gray-600 font-medium">Close Window</button>
            </div>
        </div>

    </div>

    <script>
        const qualities = <?= json_encode($qualities) ?>;
        const combos = <?= json_encode($combos) ?>;
        const images = <?= json_encode($images) ?>;
        const formFields = <?= json_encode($formFields) ?>;
        const adminPhone = "<?= $settings['admin_phone'] ?>";
        const paymentInstructions = `<?= $settings['payment_instructions'] ?>`;

        function shopApp() {
            return {
                activeSlide: 0,
                loading: false,
                showSuccess: false,
                qualities: qualities,
                combos: combos,
                images: images,
                formFields: formFields,
                paymentInstructions: paymentInstructions,
                form: {
                    name: '',
                    email: '',
                    phone: '',
                    quality_id: qualities[0]?.id,
                    combo_id: combos[0]?.id,
                    size: 'M',
                    number: '',
                    quantity: 1,
                    notes: ''
                },
                whatsappLink: '',

                init() {
                    setInterval(() => { this.nextSlide() }, 4000);
                },

                nextSlide() {
                    this.activeSlide = (this.activeSlide + 1) % this.images.length;
                },

                calculateTotal() {
                    const q = this.qualities.find(x => x.id == this.form.quality_id);
                    const c = this.combos.find(x => x.id == this.form.combo_id);
                    const price = q ? parseFloat(q.price) : 0;
                    const adjustment = c ? parseFloat(c.price_adjustment) : 0;
                    return (price + adjustment) * this.form.quantity;
                },

                async submitOrder() {
                    this.loading = true;
                    try {
                        const response = await fetch('index.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        const result = await response.json();
                        
                        if (result.success) {
                            const total = this.calculateTotal().toFixed(2);
                            const text = `Hi, I just placed Order #${result.order_id}.%0A` +
                                         `Name: ${this.form.name}%0A` +
                                         `Total: RM ${total}%0A` +
                                         `Here is my payment receipt.`;
                            this.whatsappLink = `https://wa.me/${adminPhone}?text=${text}`;
                            this.showSuccess = true;
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        alert('Something went wrong.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
