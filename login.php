<?php
session_start();
$conn = new mysqli("localhost", "root", "", "helmet");
if ($conn->connect_error) die("DB Failed: " . $conn->connect_error);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['username'] = $username;
        header("Location: data_display.php");
        exit();
    } else {
        $message = "⚠️ Invalid username or password!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body{font-family:sans-serif; background:#f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh;}
form{background:white;padding:30px;border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.2); width:300px;}
input{width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;}
button{width:100%; padding:10px; background:#2980b9; color:white; border:none; border-radius:8px; cursor:pointer;}
button:hover{background:#3498db;}
.message{color:red;text-align:center;}
p{text-align:center;}
</style>
</head>
<body>
<form method="POST">
    <h2>Login</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
    <p>Belum ada akaun? <a href="register.php">Register as new user</a></p>
</form>
</body>
</html>

