<?php
$hostname = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "towerdatabase";

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

	$id=1;
	$sql = "INSERT INTO sensor (towerid,pH, temperature, nutrientlevel, iv, timestamp) VALUES ('$id','$ph', '$t', '$w', '$iv_base64', NOW())"; 

	if (mysqli_query($conn, $sql)) { 
		echo "\nNew record created successfully"; 
	} else { 
		echo "Error: " . $sql . "<br>" . mysqli_error($conn); 
	}
}
?>
