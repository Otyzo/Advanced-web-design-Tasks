<?php
// ============================================================
// mpesa_callback.php — Daraja STK Push callback
// New Wave Jewellery
//
// Safaricom's servers POST the payment result here once the
// customer enters their PIN (or cancels/times out). This URL
// must be PUBLICLY reachable over HTTPS (see mpesa_config.php)
// — Safaricom cannot reach localhost.
//
// This endpoint is called by Safaricom, not the browser, so
// there is no session/CSRF here — it's verified instead by only
// trusting data tied to a CheckoutRequestID we generated
// ourselves and stored on an order row.
// ============================================================

require_once 'db.php';

$raw = file_get_contents('php://input');
$log = date('Y-m-d H:i:s') . " — " . $raw . "\n";
file_put_contents(__DIR__ . '/mpesa_callback.log', $log, FILE_APPEND);

$data = json_decode($raw, true);
$callback = $data['Body']['stkCallback'] ?? null;

if (!$callback) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'No callback data']);
    exit;
}

$checkoutRequestId = $callback['CheckoutRequestID'] ?? '';
$resultCode        = $callback['ResultCode'] ?? 1;
$resultDesc         = $callback['ResultDesc'] ?? '';

$mpesaReceipt = null;
if ($resultCode === 0 && !empty($callback['CallbackMetadata']['Item'])) {
    foreach ($callback['CallbackMetadata']['Item'] as $item) {
        if ($item['Name'] === 'MpesaReceiptNumber') {
            $mpesaReceipt = $item['Value'];
        }
    }
}

if ($checkoutRequestId) {
    if ($resultCode === 0) {
        // Payment succeeded
        $stmt = $conn->prepare(
            "UPDATE orders SET status = 'paid', mpesa_receipt = ?, mpesa_result_desc = ?
             WHERE mpesa_checkout_id = ?"
        );
        $stmt->bind_param('sss', $mpesaReceipt, $resultDesc, $checkoutRequestId);
        $stmt->execute();
        $stmt->close();
    } else {
        // Payment failed / cancelled / timed out
        $stmt = $conn->prepare(
            "UPDATE orders SET status = 'cancelled', mpesa_result_desc = ?
             WHERE mpesa_checkout_id = ?"
        );
        $stmt->bind_param('ss', $resultDesc, $checkoutRequestId);
        $stmt->execute();
        $stmt->close();
    }
}

// Safaricom expects this exact acknowledgement
http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
