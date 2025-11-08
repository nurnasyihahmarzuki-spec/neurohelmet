<?php
session_start();
$conn = new mysqli("localhost", "root", "", "helmet");
if ($conn->connect_error) die("DB Connection Failed: " . $conn->connect_error);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if($email && $username && $password){
        // Check if username or email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        if(!$check){
            die("Prepare failed: " . $conn->error);
        }
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $message = "⚠️ Username or Email already exists!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email, username, password, created_at) VALUES (?, ?, ?, NOW())");
            if(!$stmt){
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sss", $email, $username, $password);
            if($stmt->execute()){
                $stmt->close();
                $check->close();
                $conn->close();
                // Redirect to login after successful registration
                header("Location: login.php");
                exit();
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $message = "⚠️ Please fill in all fields!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body{font-family:sans-serif; background:#f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh;}
        form{background:white;padding:30px;border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.2);}
        input{width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;}
        button{width:100%; padding:10px; background:#2980b9; color:white; border:none; border-radius:8px; cursor:pointer;}
        button:hover{background:#3498db;}
        .message{color:red;text-align:center;}
        a{display:block;text-align:center;margin-top:10px;color:#2980b9;text-decoration:none;}
        a:hover{color:#3498db;}
    </style>
</head>
<body>
<form method="POST">
    <h2>Register New User</h2>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
    <a href="login.php">🔙 Back to Login</a>
</form>
</body>
</html>
