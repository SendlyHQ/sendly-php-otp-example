<?php
session_start();
require_once 'vendor/autoload.php';

use Sendly\Sendly;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['verification_id'])) {
    header('Location: index.php');
    exit;
}

$code = $_POST['code'] ?? '';

if (empty($code)) {
    $_SESSION['error'] = 'Verification code is required';
    header('Location: verify.php');
    exit;
}

if (!preg_match('/^[0-9]{6}$/', $code)) {
    $_SESSION['error'] = 'Invalid code format. Please enter 6 digits.';
    header('Location: verify.php');
    exit;
}

$apiKey = getenv('SENDLY_API_KEY');
if (empty($apiKey)) {
    $_SESSION['error'] = 'API key not configured. Please set SENDLY_API_KEY environment variable.';
    header('Location: verify.php');
    exit;
}

try {
    $client = new Sendly($apiKey);
    
    $result = $client->verify->check($_SESSION['verification_id'], [
        'code' => $code
    ]);
    
    if ($result->status === 'verified') {
        $phone = $_SESSION['phone'];
        
        session_unset();
        session_destroy();
        
        session_start();
        $_SESSION['verified_phone'] = $phone;
        
        header('Location: success.php');
        exit;
    } else {
        $_SESSION['error'] = 'Invalid verification code. Please try again.';
        header('Location: verify.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Verification failed: ' . $e->getMessage();
    header('Location: verify.php');
    exit;
}
