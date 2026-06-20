<?php
/**
 * ShopMart - Week 1: HTTP Request Test Endpoint
 * Used with Postman to test GET and POST requests and inspect
 * how PHP receives method, headers, and body data.
 */

header('Content-Type: application/json');

$response = [
    'method'  => $_SERVER['REQUEST_METHOD'],
    'time'    => date('c'),
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response['message'] = 'This was a GET request.';
    $response['query_params'] = $_GET;

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response['message'] = 'This was a POST request.';
    $response['form_data'] = $_POST;

} else {
    $response['message'] = 'Unsupported method for this test endpoint.';
}

echo json_encode($response, JSON_PRETTY_PRINT);
