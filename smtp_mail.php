<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

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
    // Log the request method and data
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST Data: " . print_r($_POST, true));

    // Get POST data
    $data = $_POST;
    
    // Validate required fields
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email'])) {
        throw new Exception('Required fields are missing: ' . 
            'firstName=' . (empty($data['firstName']) ? 'missing' : 'present') . ', ' .
            'lastName=' . (empty($data['lastName']) ? 'missing' : 'present') . ', ' .
            'email=' . (empty($data['email']) ? 'missing' : 'present')
        );
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format: ' . $data['email']);
    }

    error_log("Creating PHPMailer instance...");
    
    // Create a new PHPMailer instance for admin notification
    $adminMail = new PHPMailer(true);

    try {
        error_log("Configuring SMTP settings...");
        
        // Server settings for GoDaddy
        $adminMail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $adminMail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str");
        };
        
        $adminMail->isSMTP();                                           // Send using SMTP
        $adminMail->Host       = 'smtpout.secureserver.net';            // GoDaddy SMTP server
        $adminMail->SMTPAuth   = true;                                  // Enable SMTP authentication
        $adminMail->Username   = 'info@preciseotax.com';                // SMTP username
        $adminMail->Password   = 'Khobtax@2024';                        // SMTP password
        $adminMail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           // Use SSL/TLS
        $adminMail->Port       = 465;                                   // SSL/TLS port
        
        // Set timeout and keep-alive
        $adminMail->Timeout = 60;
        $adminMail->SMTPKeepAlive = true;

        // Additional security settings
        $adminMail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            )
        );
        
        error_log("Setting up email content...");
        
        // Recipients
        $adminMail->setFrom('info@preciseotax.com', 'Preciseo Tax Services');
        $adminMail->addAddress('info@preciseotax.com');

        // Content
        $adminMail->isHTML(true);
        $adminMail->Subject = 'New Consultation Request - Preciseo Tax Services';
        $adminMail->Body    = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #0066cc;'>New Consultation Request</h2>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</p>
                    <p><strong>Email:</strong> {$data['email']}</p>
                    <p><strong>Phone:</strong> {$data['phone']}</p>
                    <p><strong>Time Zone:</strong> {$data['timeZone']}</p>
                    <p><strong>Preferred Date:</strong> {$data['preferredDate']}</p>
                    <p><strong>Time Range:</strong> {$data['timeRange']}</p>
                    <p><strong>Specific Time:</strong> {$data['specificTime']}</p>
                    <p><strong>Message:</strong> {$data['message']}</p>
                </div>
            </body>
            </html>
        ";

        error_log("Attempting to send admin email...");
        
        // Send admin email
        $adminMail->send();
        error_log("Admin email sent successfully!");

        error_log("Setting up client email...");
        
        // Create a new PHPMailer instance for client confirmation
        $clientMail = new PHPMailer(true);

        // Server settings for client email
        $clientMail->isSMTP();
        $clientMail->Host       = 'smtpout.secureserver.net';            // GoDaddy SMTP server
        $clientMail->SMTPAuth   = true;
        $clientMail->Username   = 'info@preciseotax.com';
        $clientMail->Password   = 'Khobtax@2024';                        // SMTP password
        $clientMail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           // Use SSL/TLS
        $clientMail->Port       = 465;                                   // SSL/TLS port
        
        // Set timeout and keep-alive
        $clientMail->Timeout = 60;
        $clientMail->SMTPKeepAlive = true;

        // Additional security settings
        $clientMail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            )
        );
        
        // Recipients for client email
        $clientMail->setFrom('info@preciseotax.com', 'Preciseo Tax Services');
        $clientMail->addAddress($data['email'], "{$data['firstName']} {$data['lastName']}");

        // Content for client email
        $clientMail->isHTML(true);
        $clientMail->Subject = 'Thank you for your consultation request - Preciseo Tax Services';
        $clientMail->Body    = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #0066cc;'>Thank you for your consultation request!</h2>
                <p>Dear {$data['firstName']} {$data['lastName']},</p>
                <p>We have received your consultation request and will contact you shortly to confirm the appointment.</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Your requested details:</strong></p>
                    <ul>
                        <li>Date: {$data['preferredDate']}</li>
                        <li>Time Range: {$data['timeRange']}</li>
                        <li>Time Zone: {$data['timeZone']}</li>
                    </ul>
                </div>
                <p>If you need to make any changes, please contact us at:</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Phone:</strong> (551) 758-9773</p>
                    <p><strong>Email:</strong> info@preciseotax.com</p>
                </div>
                <p style='color: #666; font-size: 0.9em; margin-top: 20px;'>
                    This is an automated message. Please do not reply to this email.
                </p>
                <div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p>Best regards,<br>Preciseo Tax Services Team</p>
                </div>
            </body>
            </html>
        ";

        error_log("Attempting to send client email...");
        
        // Send client email
        $clientMail->send();
        error_log("Client email sent successfully!");

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! Your consultation request has been sent successfully.'
        ]);

    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        throw new Exception("Failed to send email: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
