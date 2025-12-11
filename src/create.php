<?php
// CouchDB cluster connection info
$couchHosts = ["couch1", "couch2", "couch3"]; // service names from docker-compose.yml
$couchPort  = "5984";
$dbName     = "testdb";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rig        = $_POST['rig'] ?? '';
    $equipment  = $_POST['equipment'] ?? '';
    $status     = $_POST['status'] ?? '';
    $technician = $_POST['technician'] ?? '';
    $notes      = $_POST['notes'] ?? '';
    $timestamp  = $_POST['timestamp'] ?? date('c');

    if ($rig && $equipment && $status && $technician) {
        $data = json_encode([
            "rig"        => $rig,
            "equipment"  => $equipment,
            "status"     => $status,
            "technician" => $technician,
            "notes"      => $notes,
            "timestamp"  => $timestamp
        ]);

        $success = false;
        $lastResponse = '';

        // Try each CouchDB host until one succeeds
        foreach ($couchHosts as $host) {
            $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode === 201) {
                $success = true;
                header("Location: index.php");
                exit;
            } else {
                $lastResponse = $response;
            }
        }

        if (!$success) {
            $message = "❌ Failed to create record on all nodes. Last response: " . htmlspecialchars($lastResponse);
        }
    } else {
        $message = "⚠ Rig, Equipment, Status, and Technician are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Maintenance Record</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Roboto',sans-serif; background:#121212; color:#e0e0e0; display:flex; justify-content:center; align-items:center; min-height:100vh; }
.card { background:#1f1f1f; padding:40px; border-radius:16px; width:450px; box-shadow:0 10px 30px rgba(0,0,0,0.5); text-align:center; }
h2 { color:#f2a900; margin-bottom:20px; }
input, select, textarea { width:100%; padding:12px; margin:8px 0; border-radius:10px; border:1px solid #333; background:#2c2c2c; color:#e0e0e0; font-size:0.95em; }
textarea { resize: vertical; }
button { margin-top:15px; width:100%; padding:12px; border:none; border-radius:10px; background:#f2a900; color:#121212; font-weight:500; cursor:pointer; }
button:hover { background:#d18e00; }
a { display:block; margin-top:20px; color:#0071e3; text-decoration:none; font-weight:500; }
a:hover { color:#005bb5; }
.message { margin-bottom:15px; font-size:0.95em; color:#ffb74d; }
</style>
</head>
<body>

<div class="card">
<h2>Add Maintenance Record</h2>

<?php if($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>

<form method="post">
    <input type="text" name="rig" placeholder="Rig Name" required>
    <input type="text" name="equipment" placeholder="Equipment" required>
    <select name="status" required>
        <option value="">Select Status</option>
        <option value="Operational">Operational</option>
        <option value="Maintenance">Maintenance</option>
        <option value="Offline">Offline</option>
        <option value="Inspection">Inspection</option>
    </select>
    <input type="text" name="technician" placeholder="Technician" required>
    <textarea name="notes" placeholder="Notes"></textarea>
    <input type="datetime-local" name="timestamp" value="<?php echo date('Y-m-d\TH:i'); ?>">

    <button type="submit">Create</button>
</form>

<a href="index.php">← Back to Dashboard</a>
</div>
</body>
</html>
