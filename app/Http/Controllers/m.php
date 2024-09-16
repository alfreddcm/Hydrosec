<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aquasec";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the tag parameter is present
if (isset($_GET['tag'])) {
    $tag = $_GET['tag'];

    $key = hex2bin("6c7b09c1a5b15b2a2c4e6d06e03e3a50d9f0a7c6b0d03c54e6793d09793438e0");
$iv = hex2bin("2b7e151628aed2a6abf7158809cf4f3c");



    $decryptedTag=do_decrypt($tag,$key,$iv);
    //diba encryted natanggap, dedecrypt lang for debugging 
    echo $decryptedTag;

    // Query to get the first and last name associated with the tag
    $sql = "SELECT first_name, last_name FROM rfid_logs WHERE tag = '$tag'"; // Assuming rfid_users table exists
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the first name and last name from the result
        $row = $result->fetch_assoc();
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];

        // Insert the log into the rfid_logs table
        $sql_log = "INSERT INTO rfid_logs (tag, first_name, last_name, timestamp) VALUES ('$tag', '$first_name', '$last_name', NOW())";
        $conn->query($sql_log);

        // Send a JSON response back to the Arduino
        $response = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
        );
        echo json_encode($response);
    } else {
        echo json_encode(array("error" => "Tag not found"));
    }
} else {
    echo json_encode(array("error" => "Tag not provided"));
}

$conn->close();


function do_encrypt($msg, $key, $iv)
{
    if (strlen($key) !== 32 || strlen($iv) !== 16) {
        return "Error: Key must be 32 bytes and IV must be 16 bytes.";
    }

    $cipher = "aes-256-cbc";
    $encrypted = openssl_encrypt($msg, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $base64_encrypted = base64_encode($encrypted);

    return $base64_encrypted;
}

function do_decrypt($encrypted_data, $key, $iv) {
    if (strlen($key) !== 32 || strlen($iv) !== 16) {
        return "Error: Key must be 32 bytes and IV must be 16 bytes.";
    }
    $cipher = "aes-256-cbc";
    $encrypted = base64_decode($encrypted_data);
    $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}
