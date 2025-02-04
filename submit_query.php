<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Log incoming request
    error_log("Received form submission: " . json_encode($_POST));

    // Get form data
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $service = $_POST['service'] ?? '';

    // Validate inputs
    if (empty($name) || empty($phone) || empty($email) || empty($message) || empty($service)) {
        throw new Exception('All fields are required');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // For testing purposes, let's save the query to a file instead of sending email
    $query_data = array(
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'service' => $service,
        'message' => $message
    );

    // Save to queries.json
    $queries_file = 'queries.json';
    $existing_queries = file_exists($queries_file) ? json_decode(file_get_contents($queries_file), true) : array();
    if (!is_array($existing_queries)) {
        $existing_queries = array();
    }
    $existing_queries[] = $query_data;
    
    if (file_put_contents($queries_file, json_encode($existing_queries, JSON_PRETTY_PRINT))) {
        // Send success response
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! We have received your query and will get back to you soon.'
        ]);
    } else {
        throw new Exception('Failed to save query data');
    }

} catch (Exception $e) {
    // Log the error
    error_log("Error in submit_query.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'There was an error processing your message: ' . $e->getMessage()
    ]);
}
?>
