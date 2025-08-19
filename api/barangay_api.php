<?php
    session_start();

    header('Content-Type: application/json');

    include "../db/conn.php";

    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    }

    $sql = "SELECT COUNT(*) AS barangay_count FROM barangay_table";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $barangay_count = $row['barangay_count'];
        echo json_encode(["count" => $barangay_count]);
    } else {
        echo json_encode(["error" => "Query failed: " . $conn->error]);
    }

    $conn->close();
?>