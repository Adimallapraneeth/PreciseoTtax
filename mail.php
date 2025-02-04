<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow from any origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Debug: Check if autoload exists
    if (!file_exists('vendor/autoload.php')) {
        throw new Exception('Composer autoload file not found. Please run composer install');
    }

    // Include PHPMailer
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    // Debug: Print POST data
    error_log("POST data: " . print_r($_POST, true));

    // Get form data with validation
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $timeZone = isset($_POST['timeZone']) ? trim($_POST['timeZone']) : '';
    $preferredDate = isset($_POST['preferredDate']) ? trim($_POST['preferredDate']) : '';
    $timeRange = isset($_POST['timeRange']) ? trim($_POST['timeRange']) : '';
    $specificTime = isset($_POST['specificTime']) ? trim($_POST['specificTime']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Debug: Print processed form data
    error_log("Processed form data:");
    error_log("First Name: $firstName");
    error_log("Last Name: $lastName");
    error_log("Email: $email");

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email)) {
        throw new Exception("Required fields are missing. First Name: $firstName, Last Name: $lastName, Email: $email");
    }

    // Debug: Verify email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format: $email");
    }

    // Create a new PHPMailer instance for admin email
    $adminMailer = new PHPMailer(true);
    
    // Debug SMTP settings
    $adminMailer->SMTPDebug = SMTP::DEBUG_SERVER;
    $adminMailer->Debugoutput = function($str, $level) {
        error_log("SMTP Debug: $str");
    };

    // Configure SMTP
    $adminMailer->isSMTP();
    $adminMailer->Host = 'smtp-mail.outlook.com';
    $adminMailer->SMTPAuth = true;
    $adminMailer->Username = 'info@preciseotax.com';
    $adminMailer->Password = 'KhobTax@2024';
    $adminMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $adminMailer->Port = 587;

    // Set email content
    $adminMailer->setFrom('info@preciseotax.com', 'Preciseo Tax Services');
    $adminMailer->addAddress('info@preciseotax.com');
    $adminMailer->isHTML(true);
    $adminMailer->Subject = "New Consultation Request - Preciseo Tax Services";
    
    // Admin email template
    $adminMailer->Body = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #0066cc;'>New Consultation Request</h2>
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;'>
            <p><strong>Name:</strong> $firstName $lastName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Time Zone:</strong> $timeZone</p>
            <p><strong>Preferred Date:</strong> $preferredDate</p>
            <p><strong>Time Range:</strong> $timeRange</p>
            <p><strong>Specific Time:</strong> $specificTime</p>
            <p><strong>Message:</strong> $message</p>
            <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
        </div>
    </body>
    </html>";

    // Send admin email
    error_log("Attempting to send admin email");
    try {
        $adminMailer->send();
        error_log("Admin email sent successfully");
    } catch (Exception $e) {
        error_log("Admin email failed: " . $e->getMessage());
        throw new Exception("Failed to send admin email: " . $e->getMessage());
    }

    // Create a new PHPMailer instance for client email
    $clientMailer = new PHPMailer(true);
    $clientMailer->isSMTP();
    $clientMailer->Host = 'smtp-mail.outlook.com';
    $clientMailer->SMTPAuth = true;
    $clientMailer->Username = 'info@preciseotax.com';
    $clientMailer->Password = 'KhobTax@2024';
    $clientMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $clientMailer->Port = 587;

    // Client email content
    $clientMailer->setFrom('info@preciseotax.com', 'Preciseo Tax Services');
    $clientMailer->addAddress($email, "$firstName $lastName");
    $clientMailer->isHTML(true);
    $clientMailer->Subject = "Thank you for your consultation request - Preciseo Tax Services";
    $clientMailer->Body = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #0066cc;'>Thank you for your consultation request!</h2>
        <p>Dear $firstName $lastName,</p>
        <p>We have received your consultation request for:</p>
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;'>
            <p><strong>Date:</strong> $preferredDate</p>
            <p><strong>Time Range:</strong> $timeRange</p>
            <p><strong>Specific Time:</strong> $specificTime</p>
            <p><strong>Time Zone:</strong> $timeZone</p>
        </div>
        <p>Our team will review your request and contact you shortly to confirm the consultation.</p>
        <p>If you need to make any changes or have questions, please contact us at:</p>
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
    </html>";

    // Send client email
    error_log("Attempting to send client email");
    try {
        $clientMailer->send();
        error_log("Client email sent successfully");
    } catch (Exception $e) {
        error_log("Client email failed: " . $e->getMessage());
        throw new Exception("Failed to send client email: " . $e->getMessage());
    }

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Emails sent successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in mail.php: " . $e->getMessage());
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
