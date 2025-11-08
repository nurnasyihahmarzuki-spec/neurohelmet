<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "helmet");
if ($conn->connect_error) {
    die("DB Failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $helmet_id = trim($_POST['helmet_id']);
    $helmet_name = trim($_POST['helmet_name']);

    if ($helmet_id !== "" && $helmet_name !== "") {
        // Check if helmet already exists
        $check = $conn->prepare("SELECT helmet_id FROM helmets WHERE helmet_id = ?");
        $check->bind_param("s", $helmet_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "⚠️ Helmet ID already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO helmets (helmet_id, helmet_name, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $helmet_id, $helmet_name);

            if ($stmt->execute()) {
                $message = "✅ Helmet registered successfully!";
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

// Fetch all helmets
$result = $conn->query("SELECT helmet_id, helmet_name, created_at FROM helmets ORDER BY created_at DESC");
$helmets = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $helmets[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Helmet Register</title>
    <style>
        body {
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('kimak.jpg') no-repeat center center fixed;
            background-size: cover;
            color:white;
        }
        .navbar {
            background:rgba(44,62,80,0.95);
            display:flex;
            justify-content:center;
            align-items:center;
            padding:15px 40px;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            box-shadow:0 4px 10px rgba(0,0,0,0.3);
            z-index:1000;
        }
        .navbar-center {
            font-size:1.6em;
            font-weight:bold;
            letter-spacing:1px;
            color:white;
        }
        .navbar-links {
            position:absolute;
            right:80px;
            display:flex;
            gap:15px;
        }
        .navbar-links a {
            color:white;
            text-decoration:none;
            padding:10px 18px;
            background:#2980b9;
            border-radius:8px;
            transition:0.3s;
        }
        .navbar-links a:hover { background:#3498db; }
        .main { padding:120px 40px 40px; }
        .card {
            background:rgba(255,255,255,0.9);
            color:black;
            padding:25px;
            border-radius:15px;
            box-shadow:0 8px 20px rgba(0,0,0,0.15);
            margin-bottom:25px;
        }
        table {
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }
        table thead { background:#2980b9; color:white; }
        table th, td { padding:12px; text-align:center; }
        table tr:nth-child(even){background:#e6f2fb;}
        table tr:hover{background:#d0e7f9;}
        .form-group { margin-bottom:15px; }
        input[type="text"] {
            width:100%;
            padding:10px;
            border-radius:8px;
            border:1px solid #ccc;
            font-size:1em;
        }
        button {
            padding:10px 20px;
            background:#2980b9;
            color:white;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:1em;
            transition:0.3s;
        }
        button:hover { background:#3498db; }
        .message {
            text-align:center;
            padding:10px;
            font-weight:bold;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-center">NEURO</div>
        <div class="navbar-links">
            <a href="data_display.php">📊 Data Display</a>
            <a href="helmet_register.php">⛑ Helmet Register</a>
            <a href="logout.php" onclick="return confirm('Logout?')">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <h1>Register New Helmet</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Helmet ID:</label>
                    <input type="text" name="helmet_id" required>
                </div>
                <div class="form-group">
                    <label>Helmet Name:</label>
                    <input type="text" name="helmet_name" required>
                </div>
                <button type="submit">Register Helmet</button>
            </form>
        </div>

        <div class="card">
            <h2>Registered Helmets</h2>
            <table>
                <thead>
                    <tr>
                        <th>Helmet ID</th>
                        <th>Helmet Name</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($helmets)): ?>
                        <?php foreach ($helmets as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['helmet_id']); ?></td>
                                <td><?= htmlspecialchars($h['helmet_name']); ?></td>
                                <td><?= htmlspecialchars($h['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No helmets registered yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
