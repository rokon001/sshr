<?php
/**
 * Contact Form Handler
 * Processes contact form submissions
 */

header('Content-Type: application/json');

require_once 'config/database.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$turnstileToken = $_POST['cf-turnstile-response'] ?? '';

// Validate required fields
$errors = [];

if (empty($name)) {
    $errors[] = 'Ime je obavezno';
}

if (empty($email)) {
    $errors[] = 'Email je obavezan';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email adresa nije ispravna';
}

if (empty($message)) {
    $errors[] = 'Poruka je obavezna';
}

// Verify Turnstile token
if (!empty($turnstileToken)) {
    $turnstileSecret = '0x4AAAAAACAsbam0KqzsMjxQ9thDQnn0e8U'; // Move to settings in production
    
    $verifyData = [
        'secret' => $turnstileSecret,
        'response' => $turnstileToken,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($verifyData));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (!$result['success']) {
        $errors[] = 'Verifikacija nije uspjela. Molimo pokušajte ponovno.';
    }
}

// Return errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Save to database if available
if (dbAvailable()) {
    try {
        $stmt = db()->prepare("
            INSERT INTO contact_submissions (name, email, phone, message, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $name,
            $email,
            $phone,
            $message,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Log error but continue
        error_log('Database error: ' . $e->getMessage());
    }
}

// Send email notification
$to = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'info@startsmarthr.eu';
$subject = 'Nova poruka s web stranice - ' . $name;

$emailBody = "Nova poruka s kontakt forme:\n\n";
$emailBody .= "Ime: $name\n";
$emailBody .= "Email: $email\n";
$emailBody .= "Telefon: $phone\n\n";
$emailBody .= "Poruka:\n$message\n\n";
$emailBody .= "---\n";
$emailBody .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
$emailBody .= "Vrijeme: " . date('d.m.Y H:i:s') . "\n";

$headers = [
    'From: noreply@startsmarthr.eu',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Try to send email
$emailSent = @mail($to, $subject, $emailBody, implode("\r\n", $headers));

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Poruka uspješno poslana! Javit ćemo vam se uskoro.'
]);

