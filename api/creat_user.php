<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Example: validate and save to DB...
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully.'
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>