<?php




 // do decryption:
//  echo "<br>Decrypt:";
//  $result=base64_decode($result);
//  $result = openssl_decrypt($result, $method, $key, OPENSSL_NO_PADDING,$iv);
//  $decoded_msg=base64_decode($result);
//  var_dump($decoded_msg);

// Database connection parameters
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "hydro"; // Name of the database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$method="AES-128-CBC";
$key="aaaaaaaaaaaaaaaa";
$iv=$key; // dont do this in real application!
$msg="longdt9@gmail.com, Iot AES-128-CBC encryption test!";

// encode the message with base64 encoder (optional, make the same as we did with Arduino).
$msg=base64_encode($msg);
// zero-padding:
$str_padded = $msg;
$pad=16 - strlen($str_padded) % 16;
if (strlen($str_padded) % 16) {
    $str_padded = str_pad($str_padded,strlen($str_padded)+$pad, "\0");
}
// do encryption:
$result = openssl_encrypt($str_padded, $method, $key, OPENSSL_NO_PADDING,$iv); // OPENSSL_NO_PADDING is important parameter
$result = base64_encode($result);
$iv=base64_encode($iv);
echo "Encrypted data:";
var_dump($result);


// Store in the database
$sql = "INSERT INTO encrypted_data3 (data, iv, timestamp) VALUES ('$result', '$iv', NOW())";
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>



<?php
$hostname = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "sensor_data"; 

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) { 
	die("Connection failed: " . mysqli_connect_error()); 
} 

echo "Database connection is OK<br>"; 

if(isset($_POST["phValue"]) && isset($_POST["temp"]) && isset($_POST["waterLevel"])) {
	$ph = $_POST["phValue"];
	$t = $_POST["temp"];
	$w = $_POST["waterLevel"];
	$iv = "aaaaaaaaaaaaaaaa"; 
    $iv_base64 = base64_encode($iv);

	echo $ph;
	echo $t;
	echo $w;

// 	$sql = "INSERT INTO measurements (ph, temp, waterlevel, iv, timestamp) VALUES ('$ph', '$t', '$w', '$iv_base64', NOW())"; 

// 	if (mysqli_query($conn, $sql)) { 
// 		echo "\nNew record created successfully"; 
// 	} else { 
// 		echo "Error: " . $sql . "<br>" . mysqli_error($conn); 
// 	}
// }
?>

////////


<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "sensor_data"; // Name of the database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the most recent encrypted data from the database

$sql = "SELECT ph, temp, waterlevel, iv FROM measurements ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of the most recent row
    $row = $result->fetch_assoc();
    $encrypted_ph = $row['ph'];
    $encrypted_temp = $row['temp'];
    $encrypted_waterlevel = $row['waterlevel'];
    
    $iv_base64 = $row['iv'];
    $iv = base64_decode($iv_base64);
    $method = "AES-128-CBC";
    $key = "aaaaaaaaaaaaaaaa"; 

    function decrypt_data($encrypted_data, $method, $key, $iv) {
        $encrypted_data = base64_decode($encrypted_data);

        $decrypted_data = openssl_decrypt($encrypted_data, $method, $key, OPENSSL_NO_PADDING, $iv);
        $decrypted_data = rtrim($decrypted_data, "\0");
        $decoded_msg = base64_decode($decrypted_data);
        return $decoded_msg;
    }

    $decrypted_ph = decrypt_data($encrypted_ph, $method, $key, $iv);
    $decrypted_temp = decrypt_data($encrypted_temp, $method, $key, $iv);
    $decrypted_waterlevel = decrypt_data($encrypted_waterlevel, $method, $key, $iv);

    // Print the decrypted data
    echo "Decrypted ph: " . $decrypted_ph . "<br>";
    echo "Decrypted temp: " . $decrypted_temp . "<br>";
    echo "Decrypted water level: " . $decrypted_waterlevel;

} else {
    echo "No data found";
}

// Close the connection
$conn->close();
}





///////////

@php

// Decrypt data
$method = "AES-128-CBC";
$key = "aaaaaaaaaaaaaaaa";
$ivv = base64_decode($key);


// Function to decrypt each variable
function decrypt_data($encrypted_data, $method, $key, $ivv) {
    // Decode the Base64-encoded encrypted data
    $encrypted_data = base64_decode($encrypted_data);
    $decrypted_data = openssl_decrypt($encrypted_data, $method, $key, OPENSSL_NO_PADDING, $ivv);
    $decrypted_data = rtrim($decrypted_data, "\0");
    $decoded_msg = base64_decode($decrypted_data);
    return $decoded_msg;
}

// Decrypt each variable
$encrypted_ph='86DcS/7U80sZuVCf2QIT0w==';
$decrypted_ph = decrypt_data($encrypted_ph, $method, $key, $ivv);


// Print the decrypted data
echo "Decrypted ph: " . $decrypted_ph . "<br>";
@endphp
