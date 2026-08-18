<?php
// eSewa Sandbox Credentials
define('ESEWA_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY', '8gBmStructureKeySecretSecret=='); // Test Secret Key
define('ESEWA_PAYMENT_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');

// Return URLs
define('ESEWA_SUCCESS_URL', 'http://localhost/success.php');
define('ESEWA_FAILURE_URL', 'http://localhost/failure.php');

/**
 * Generate HMAC SHA256 Signature required by eSewa v2.0
 */
function generate_esewa_signature($total_amount, $transaction_uuid, $product_code) {
    $data_string = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
    $s = hash_hmac('sha256', $data_string, ESEWA_SECRET_KEY, true);
    return base64_encode($s);
}