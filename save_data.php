<?php
$conn = new mysqli("localhost", "root", "", "helmet");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;

    $stmt = $conn->prepare("INSERT INTO data_info (latitude, longitude, reading_time) VALUES (?, ?, NOW())");
    $stmt->bind_param("dd", $latitude, $longitude);

    if ($stmt->execute()) echo "✅ Data saved successfully!";
    else echo "❌ Error: " . $stmt->error;

    $stmt->close();
}
$conn->close();
?>
