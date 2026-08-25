<?php
// ============================================================
// mpesa_helper.php — Daraja API helper functions
// New Wave Jewellery
//
// Handles:
//   - OAuth access token retrieval
//   - STK Push (Lipa Na M-Pesa Online) request
//   - Phone number normalisation to 2547XXXXXXXX format
// ============================================================

require_once 'mpesa_config.php';

/**
 * Normalise a Kenyan phone number to the 2547XXXXXXXX / 2541XXXXXXXX
 * format Daraja requires. Accepts 07..., 01..., +254..., 254...
 * Returns false if the number doesn't look valid.
 */
function mpesa_format_phone(string $phone) {
    $phone = preg_replace('/[^0-9]/', '', trim($phone)); // strip spaces, +, dashes

    if (preg_match('/^0(7|1)\d{8}$/', $phone)) {          // 07XXXXXXXX / 01XXXXXXXX
        return '254' . substr($phone, 1);
    }
    if (preg_match('/^254(7|1)\d{8}$/', $phone)) {         // already 254...
        return $phone;
    }
    if (preg_match('/^(7|1)\d{8}$/', $phone)) {            // 7XXXXXXXX (no leading 0)
        return '254' . $phone;
    }
    return false;
}

/**
 * Get an OAuth access token from Daraja using the Consumer
 * Key/Secret in mpesa_config.php. Returns the token string,
 * or throws an Exception on failure.
 */
function mpesa_get_access_token(): string {
    $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        throw new Exception("Could not reach Safaricom (network error): $err");
    }

    $data = json_decode($response, true);
    if ($httpCode !== 200 || empty($data['access_token'])) {
        throw new Exception('Failed to get M-Pesa access token: ' . ($response ?: 'no response'));
    }

    return $data['access_token'];
}

/**
 * Trigger an STK Push (the payment prompt on the customer's phone).
 *
 * @param string $phone       Customer phone, any common format (will be normalised)
 * @param float  $amount      Amount in KES (whole numbers only — Daraja sandbox rejects decimals)
 * @param string $accountRef  Short reference shown on the STK prompt (e.g. order number)
 * @param string $description Short transaction description
 * @return array               Decoded Daraja response (contains CheckoutRequestID etc.)
 * @throws Exception           On any failure (invalid phone, network error, Daraja error)
 */
function mpesa_stk_push(string $phone, float $amount, string $accountRef, string $description = 'New Wave Jewellery'): array {
    $formattedPhone = mpesa_format_phone($phone);
    if (!$formattedPhone) {
        throw new Exception('Invalid phone number. Use format 07XXXXXXXX or 2547XXXXXXXX.');
    }

    $accessToken = mpesa_get_access_token();

    $timestamp = date('YmdHis');
    $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => (int)round($amount), // sandbox rejects decimals
        'PartyA'            => $formattedPhone,
        'PartyB'            => MPESA_SHORTCODE,
        'PhoneNumber'       => $formattedPhone,
        'CallBackURL'       => MPESA_CALLBACK_URL,
        'AccountReference'  => substr($accountRef, 0, 12),
        'TransactionDesc'   => substr($description, 0, 13),
    ];

    $ch = curl_init(MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new Exception("Could not reach Safaricom (network error): $err");
    }

    $data = json_decode($response, true);

    if (empty($data['ResponseCode']) || $data['ResponseCode'] !== '0') {
        $msg = $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'Unknown error from Safaricom';
        throw new Exception("M-Pesa STK push failed: $msg");
    }

    return $data; // contains MerchantRequestID, CheckoutRequestID, ResponseDescription...
}
