<?php
// CouchDB connection info
$url = "http://admin:admin@192.168.49.2:30084/testdb";

$message = '';
$id = $_POST['id'] ?? '';

// Load the document
$doc = null;
$rev = null;

if ($id) {
    $ch = curl_init("$url/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);

    if ($response !== false) {
        $doc = json_decode($response, true);
        $rev = $doc['_rev'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $rig        = $_POST['rig'] ?? '';
    $equipment  = $_POST['equipment'] ?? '';
    $status     = $_POST['status'] ?? '';
    $technician = $_POST['technician'] ?? '';
    $notes      = $_POST['notes'] ?? '';
    $timestamp  = $_POST['timestamp'] ?? date('c');

    if ($rig && $equipment && $status && $technician) {
        $updatedDoc = json_encode([
            "_id"       => $id,
            "_rev"      => $rev,
            "rig"       => $rig,
            "equipment" => $equipment,
            "status"    => $status,
            "technician"=> $technician,
            "notes"     => $notes,
            "timestamp" => $timestamp
        ]);

        $ch = curl_init("$url/$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updatedDoc);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $updateResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 201 || $httpCode === 200) {
            header("Location: crud.php");
            exit;
        } else {
            $message = "❌ Update failed: $updateResponse";
        }
    } else {
        $message = "⚠ Rig, Equipment, Status, and Technician cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Maintenance Record</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #f2f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    width: 450px;
    text-align: center;
}
input, textarea, select {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 0.95em;
}
textarea {
    resize: vertical;
}
button {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background: #ff9500;
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    cursor: pointer;
}
button:hover { background: #cc7a00; }
a {
    display: block;
    margin-top: 20px;
    color: #0071e3;
    font-weight: 600;
    text-decoration: none;
}
a:hover { color: #005bb5; }
</style>
</head>
<body>

<div class="card">
    <h2>Update Maintenance Record</h2>

    <?php if ($doc): ?>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

            <input type="text" name="rig" value="<?php echo htmlspecialchars($doc['rig'] ?? ''); ?>" placeholder="Rig Name" required>
            <input type="text" name="equipment" value="<?php echo htmlspecialchars($doc['equipment'] ?? ''); ?>" placeholder="Equipment" required>
            
            <select name="status" required>
                <option value="">Select Status</option>
                <?php
                $statuses = ['Operational', 'Maintenance', 'Offline', 'Inspection'];
                foreach ($statuses as $s) {
                    $selected = ($doc['status'] ?? '') === $s ? 'selected' : '';
                    echo "<option value='$s' $selected>$s</option>";
                }
                ?>
            </select>

            <input type="text" name="technician" value="<?php echo htmlspecialchars($doc['technician'] ?? ''); ?>" placeholder="Technician" required>
            <textarea name="notes" placeholder="Notes"><?php echo htmlspecialchars($doc['notes'] ?? ''); ?></textarea>
            <input type="datetime-local" name="timestamp" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($doc['timestamp'] ?? 'now'))); ?>">

            <button type="submit" name="update">Update</button>
        </form>
    <?php else: ?>
        <p>⚠ Unable to load document.</p>
    <?php endif; ?>

    <a href="crud.php">← Back to Dashboard</a>
</div>

</body>
</html>
