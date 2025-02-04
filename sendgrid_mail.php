<?php
require 'vendor/autoload.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON response
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get POST data
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email'])) {
        throw new Exception('Required fields are missing');
    }

    // Initialize SendGrid
    $sendgrid = new \SendGrid('YOUR_SENDGRID_API_KEY');
    $email = new \SendGrid\Mail\Mail();

    // Admin Email
    $email->setFrom("info@preciseotax.com", "Preciseo Tax Services");
    $email->setSubject("New Consultation Request");
    $email->addTo("info@preciseotax.com", "Admin");
    
    // Admin email content
    $adminContent = "
    <h2>New Consultation Request</h2>
    <p><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</p>
    <p><strong>Email:</strong> {$data['email']}</p>
    <p><strong>Phone:</strong> {$data['phone']}</p>
    <p><strong>Time Zone:</strong> {$data['timeZone']}</p>
    <p><strong>Preferred Date:</strong> {$data['preferredDate']}</p>
    <p><strong>Time Range:</strong> {$data['timeRange']}</p>
    <p><strong>Specific Time:</strong> {$data['specificTime']}</p>
    <p><strong>Message:</strong> {$data['message']}</p>
    ";
    
    $email->addContent("text/html", $adminContent);
    
    // Send admin email
    $response = $sendgrid->send($email);
    
    if ($response->statusCode() !== 202) {
        throw new Exception('Failed to send admin email');
    }

    // Client confirmation email
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("info@preciseotax.com", "Preciseo Tax Services");
    $email->setSubject("Thank you for your consultation request");
    $email->addTo($data['email'], "{$data['firstName']} {$data['lastName']}");
    
    // Client email content
    $clientContent = "
    <h2>Thank you for your consultation request!</h2>
    <p>Dear {$data['firstName']} {$data['lastName']},</p>
    <p>We have received your consultation request and will contact you shortly to confirm the appointment.</p>
    <p>Your requested details:</p>
    <ul>
        <li>Date: {$data['preferredDate']}</li>
        <li>Time Range: {$data['timeRange']}</li>
        <li>Time Zone: {$data['timeZone']}</li>
    </ul>
    <p>If you need to make any changes, please contact us at:</p>
    <p>Phone: (551) 758-9773</p>
    <p>Email: info@preciseotax.com</p>
    <br>
    <p>Best regards,<br>Preciseo Tax Services Team</p>
    ";
    
    $email->addContent("text/html", $clientContent);
    
    // Send client email
    $response = $sendgrid->send($email);
    
    if ($response->statusCode() !== 202) {
        throw new Exception('Failed to send client email');
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Emails sent successfully'
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
