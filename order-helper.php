<?php
/**
 * Generate Custom Order Number
 * Format: TRU-YYMM-XXXX
 * Example: TRU-2411-7294
 */
function generateOrderNumber() {
    $year = date('y');  // 2-digit year
    $month = date('m'); // 2-digit month
    $random = rand(1000, 9999); // Random 4-digit number
    
    return "TRU-{$year}{$month}-{$random}";
}

/**
 * Check if order number already exists (rare but possible with random)
 * If exists, regenerate
 */
function getUniqueOrderNumber($pdo) {
    $maxAttempts = 10;
    $attempt = 0;
    
    do {
        $orderNumber = generateOrderNumber();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $exists = $stmt->fetchColumn() > 0;
        $attempt++;
    } while ($exists && $attempt < $maxAttempts);
    
    if ($exists) {
        // Fallback to timestamp-based if random fails
        return "TRU-" . date('ymd-His');
    }
    
    return $orderNumber;
}
?>
