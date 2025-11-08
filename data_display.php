<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Sambung ke database
$conn = new mysqli("localhost", "root", "", "helmet");
if ($conn->connect_error) {
    die("DB Failed: " . $conn->connect_error);
}

// Ambil data GPS
$sql = "SELECT id, latitude, longitude, reading_time FROM data_info ORDER BY reading_time DESC";
$result = $conn->query($sql);
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$latest = !empty($data) ? $data[0] : null;

// Ambil senarai helmet berdaftar
$helmet_sql = "SELECT * FROM helmets ORDER BY created_at DESC";
$helmet_result = $conn->query($helmet_sql);
$helmets = [];
if ($helmet_result && $helmet_result->num_rows > 0) {
    while ($row = $helmet_result->fetch_assoc()) {
        $helmets[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Helmet Data Display</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        body { margin:0; font-family:'Segoe UI',sans-serif; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('kimak.jpg') no-repeat center center fixed; background-size: cover; color:white; }
        .navbar { background:rgba(44,62,80,0.95); display:flex; justify-content:center; align-items:center; padding:15px 40px; position:fixed; top:0; left:0; width:100%; box-shadow:0 4px 10px rgba(0,0,0,0.3); z-index:1000; }
        .navbar-center { font-size:1.6em; font-weight:bold; letter-spacing:1px; color:white; }
        .navbar-links { position:absolute; right:80px; display:flex; gap:15px; }
        .navbar-links a { color:white; text-decoration:none; padding:10px 18px; background:#2980b9; border-radius:8px; transition:0.3s; }
        .navbar-links a:hover { background:#3498db; }
        .main { padding:120px 40px 40px; }
        .card { background:rgba(255,255,255,0.9); color:black; padding:25px; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.15); margin-bottom:25px; }
        h1, h2 { text-align:center; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        table thead { background:#2980b9; color:white; }
        table th, td { padding:12px; text-align:center; }
        table tr:nth-child(even){background:#e6f2fb;}
        table tr:hover{background:#d0e7f9;}
        #map { height:500px; width:100%; border-radius:12px; margin-top:15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-center">NEURO</div>
        <div class="navbar-links">
            <a href="helmet_register.php">📝 Register Helmet</a>
            <a href="data_display.php">📊 Data Display</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <h1>Helmet GPS Monitoring</h1>

        <!-- Registered Helmets -->
        <div class="card">
            <h2>🪖 Registered Helmets</h2>
            <table>
                <thead>
                    <tr>
                        <th>Helmet ID</th>
                        <th>Helmet Name</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($helmets)) { 
                        foreach($helmets as $h) { ?>
                            <tr>
                                <td><?= htmlspecialchars($h['helmet_id']); ?></td>
                                <td><?= htmlspecialchars($h['helmet_name']); ?></td>
                                <td><?= htmlspecialchars($h['created_at']); ?></td>
                            </tr>
                    <?php } } else { ?>
                        <tr><td colspan="3">No helmets registered yet</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- GPS Data -->
        <div class="card">
            <h2>📍 Helmet GPS Data</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Reading Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data)) {
                        foreach($data as $row) { ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= $row['latitude']; ?></td>
                                <td><?= $row['longitude']; ?></td>
                                <td><?= $row['reading_time']; ?></td>
                            </tr>
                    <?php } } else { ?>
                        <tr><td colspan="4">No GPS data found</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Map -->
        <div class="card">
            <h2>🗺 Live Location Map</h2>
            <div id="map"></div>
        </div>
    </div>

    <script>
var map = L.map('map').setView([0, 0], 2); // default world view

var normalMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'&copy; OpenStreetMap contributors'
}).addTo(map);

var satelliteMap = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    attribution:'Map data ©️ Google'
});

L.control.layers({"🌍 Normal": normalMap, "🛰 Satellite": satelliteMap}).addTo(map);

// Dapatkan lokasi semasa pengguna
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;

        // Pindahkan view map ke lokasi semasa
        map.setView([lat, lng], 15);

        // Marker lokasi semasa
        L.marker([lat, lng]).addTo(map)
            .bindPopup("📍 Your current location")
            .openPopup();

        // Circle di sekitar lokasi
        L.circle([lat, lng], {radius:50, color:'#e74c3c', fillColor:'#e74c3c', fillOpacity:0.3}).addTo(map);

    }, function(error) {
        console.log("Error getting location: ", error);
    });
} else {
    alert("Geolocation is not supported by your browser.");
}

// Masukkan juga data helmet GPS dari database
<?php
if(!empty($data)) {
    foreach($data as $i => $d) {
        $color = ($i === 0) ? "#3498db" : "#2980b9";
        echo "L.circleMarker([{$d['latitude']}, {$d['longitude']}], {radius:8,color:'$color',fillColor:'$color',fillOpacity:0.9}).addTo(map).bindPopup('ID: {$d['id']}<br>Lat: {$d['latitude']}<br>Lng: {$d['longitude']}<br>Time: {$d['reading_time']}');\n";
    }
}
?>
</script>

<script>
function sendAlert(phone, name) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var mapLink = `https://www.google.com/maps?q=${lat},${lng}`;
            var message = `🚨 EMERGENCY ALERT!\n\nContact: ${name}\nLocation: ${mapLink}\n\nPlease respond immediately.`;
            var whatsappURL = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
            window.open(whatsappURL, '_blank');
        }, function(error) {
            alert("Unable to get current location.");
        });
    } else {
        alert("Geolocation not supported.");
    }

}
 
</script>


<!-- Emergency Contact -->
<div class="card">
    <h2>📞 Emergency Contacts</h2>
    <form method="POST" action="">
        <input type="text" name="contact_name" placeholder="Contact Name" required>
        <input type="text" name="contact_number" placeholder="Contact Number (e.g. 60123456789)" required>
        <button type="submit" name="add_contact">Add Contact</button>
    </form>

    <table>
        <thead>
            <tr><th>Name</th><th>Number</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
            $conn = new mysqli("localhost", "root", "", "helmet");
            if (isset($_POST['add_contact'])) {
                $name = $_POST['contact_name'];
                $num = $_POST['contact_number'];
                $conn->query("INSERT INTO emergency_contacts (contact_name, contact_number) VALUES ('$name', '$num')");
            }
            if (isset($_GET['delete'])) {
                $id = $_GET['delete'];
                $conn->query("DELETE FROM emergency_contacts WHERE id=$id");
            }
            $res = $conn->query("SELECT * FROM emergency_contacts ORDER BY id DESC");
            if ($res->num_rows > 0) {
                while ($r = $res->fetch_assoc()) {
                    echo "<tr>
                        <td>{$r['contact_name']}</td>
                        <td>{$r['contact_number']}</td>
                        <td>
                            <a href='?delete={$r['id']}' onclick=\"return confirm('Delete this contact?')\">❌</a>
                            <button type='button' onclick=\"sendAlert('{$r['contact_number']}','{$r['contact_name']}')\">🚨 Send Alert</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No contacts yet</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

