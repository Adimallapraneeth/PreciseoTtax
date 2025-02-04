<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    
    // Admin email
    $to = "info@preciseotax.com";
    $subject = "New Consultation Request";
    
    $message = "
    <html>
    <head>
        <title>New Consultation Request</title>
    </head>
    <body>
        <h2>New Consultation Request</h2>
        <p><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>
        <p><strong>Phone:</strong> {$data['phone']}</p>
        <p><strong>Time Zone:</strong> {$data['timeZone']}</p>
        <p><strong>Preferred Date:</strong> {$data['preferredDate']}</p>
        <p><strong>Time Range:</strong> {$data['timeRange']}</p>
        <p><strong>Specific Time:</strong> {$data['specificTime']}</p>
        <p><strong>Message:</strong> {$data['message']}</p>
    </body>
    </html>
    ";
    
    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Preciseo Tax Services <info@preciseotax.com>\r\n";
    
    // Send email
    $mailSent = mail($to, $subject, $message, $headers);
    
    if ($mailSent) {
        // Send confirmation email to client
        $clientSubject = "Thank you for your consultation request";
        $clientMessage = "
        <html>
        <head>
            <title>Consultation Request Received</title>
        </head>
        <body>
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
        </body>
        </html>
        ";
        
        mail($data['email'], $clientSubject, $clientMessage, $headers);
        
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
