<?php
// CouchDB cluster connection info
$couchHosts = ["couch1", "couch2", "couch3"]; // service names from docker-compose.yml
$couchPort  = "5984";
$dbName     = "testdb";

$message = '';
$doc = null;

// Get the document ID from POST
$id = $_POST['id'] ?? '';

// If form submitted with updated data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rig'])) {
    $id = $_POST['id'];
    $rev = null;
    $lastResponse = '';

    // Fetch current revision from any available node
    foreach ($couchHosts as $host) {
        $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}/{$id}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 200 && $response) {
            $doc = json_decode($response, true);
            $rev = $doc['_rev'] ?? null;
            break;
        } else {
            $lastResponse = $response;
        }
    }

    if ($rev) {
        // Prepare updated document
        $updatedDoc = [
            "_id"        => $id,
            "_rev"       => $rev,
            "rig"        => $_POST['rig'],
            "equipment"  => $_POST['equipment'],
            "status"     => $_POST['status'],
            "technician" => $_POST['technician'],
            "timestamp"  => $_POST['timestamp']
        ];

        $success = false;

        // Try to update on any available node
        foreach ($couchHosts as $host) {
            $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}/{$id}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updatedDoc));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $updateResponse = curl_exec($ch);
            $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($updateCode === 201) {
                $message = "✅ Document updated successfully!";
                $success = true;
                header("Refresh:2; url=index.php");
                break;
            } else {
                $lastResponse = $updateResponse;
            }
        }

        if (!$success) {
            $message = "❌ Failed to update document on all nodes. Last response: " . htmlspecialchars($lastResponse);
        }
    } else {
        $message = "⚠ Could not fetch document revision.";
    }
} elseif ($id) {
    // Fetch document for editing from any available node
    foreach ($couchHosts as $host) {
        $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}/{$id}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 200 && $response) {
            $doc = json_decode($response, true);
            break;
        }
    }
    if (!$doc) {
        $message = "⚠ Document not found.";
    }
} else {
    $message = "⚠ No document ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Document</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #121212;
    color: #e0e0e0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.card {
    background-color: #1f1f1f;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    max-width: 600px;
    width: 100%;
    text-align: center;
}
h2 {
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 1.8em;
    color: #f2a900; /* gold accent */
}
.message {
    font-weight: 600;
    margin-bottom: 20px;
    font-size: 1.1em;
    color: #ff3b30;
}
form { text-align: left; }
label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
    color: #e0e0e0;
}
input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solid #333;
    background-color: #2c2c2c;
    color: #e0e0e0;
}
.button {
    display: inline-block;
    padding: 10px 25px;
    border-radius: 10px;
    border: none;
    font-weight: 500;
    font-size: 0.95em;
    cursor: pointer;
    transition: 0.2s ease-in-out;
    margin-top: 20px;
}
.update { background-color: #0071e3; color: #fff; }
.update:hover { background-color: #005bb5; }
.back { background-color: #f2a900; color: #121212; }
.back:hover { background-color: #d18e00; }
</style>
</head>
<body>
<div class="card">
    <h2>Update Document</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($doc): ?>
        <form method="post" action="update.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($doc['_id']); ?>">

            <label for="rig">Rig</label>
            <input type="text" id="rig" name="rig" value="<?php echo htmlspecialchars($doc['rig'] ?? ''); ?>">

            <label for="equipment">Equipment</label>
            <input type="text" id="equipment" name="equipment" value="<?php echo htmlspecialchars($doc['equipment'] ?? ''); ?>">

            <label for="status">Status</label>
            <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($doc['status'] ?? ''); ?>">

            <label for="technician">Technician</label>
            <input type="text" id="technician" name="technician" value="<?php echo htmlspecialchars($doc['technician'] ?? ''); ?>">

            <label for="timestamp">Timestamp</label>
            <input type="text" id="timestamp" name="timestamp" value="<?php echo htmlspecialchars($doc['timestamp'] ?? ''); ?>">

            <button class="button update" type="submit">Save Changes</button>
        </form>
    <?php endif; ?>

    <form method="get" action="index.php">
        <button class="button back">← Back to Dashboard</button>
    </form>
</div>
</body>
</html>
