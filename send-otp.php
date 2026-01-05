<?php
session_start();
require_once 'vendor/autoload.php';

use Sendly\Sendly;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$phone = $_POST['phone'] ?? '';

if (empty($phone)) {
    $_SESSION['error'] = 'Phone number is required';
    header('Location: index.php');
    exit;
}

if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
    $_SESSION['error'] = 'Invalid phone number format. Please include country code.';
    header('Location: index.php');
    exit;
}

$apiKey = getenv('SENDLY_API_KEY');
if (empty($apiKey)) {
    $_SESSION['error'] = 'API key not configured. Please set SENDLY_API_KEY environment variable.';
    header('Location: index.php');
    exit;
}

try {
    $client = new Sendly($apiKey);
    
    $response = $client->verify->send([
        'to' => $phone
    ]);
    
    $_SESSION['verification_id'] = $response->id;
    $_SESSION['phone'] = $phone;
    
    header('Location: verify.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to send verification code: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
