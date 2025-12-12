<?php
// CouchDB cluster connection info
$couchHosts = ["couch1", "couch2", "couch3"]; // service names from docker-compose.yml
$couchPort  = "5984";
$dbName     = "testdb";

$message = '';
$doc = null;
$offlineMode = false;

// Get the document ID from POST (or keep empty)
$id = $_POST['id'] ?? '';

// If form submitted with updated data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rig'])) {
    $id = $_POST['id'] ?? $id;
    $rev = null;
    $lastResponse = '';

    // Try to fetch current revision from any available node
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
            $offlineMode = true;
            $message = "⚠ Cluster unavailable. Switching to offline update.";
        }
    } else {
        $offlineMode = true;
        $message = "⚠ Could not fetch document revision. Attempting offline update.";
    }
} elseif (!empty($id)) {
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
        $offlineMode = true;
        $message = "⚠ Document not found on cluster. Attempting offline edit.";
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
    color: #f2a900;
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
.offline-note {
    margin-top: 10px;
    font-size: 0.9em;
    color: #ffb74d;
}
</style>
</head>
<body>
<div class="card">
    <h2>Update Document</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($doc || ($offlineMode && !empty($id))): ?>
        <form method="post" action="update.php" id="update-form">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($doc['_id'] ?? $id); ?>">

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
        <?php if ($offlineMode): ?>
            <p class="offline-note">You are editing locally. Changes will sync when CouchDB is reachable.</p>
        <?php endif; ?>
    <?php endif; ?>

    <form method="get" action="index.php">
        <button class="button back">← Back to Dashboard</button>
    </form>
</div>

<?php if ($offlineMode): ?>
<!-- Offline update via PouchDB -->
<script src="https://cdn.jsdelivr.net/npm/pouchdb@9.0.0/dist/pouchdb.min.js"></script>
<script>
const localDB = new PouchDB('testdb');
(async () => {
    try {
        const id = <?php echo json_encode($id); ?>;

        // If just viewing the form (GET), load local doc data into fields
        <?php if (!($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rig']))): ?>
        try {
            const localDoc = await localDB.get(id);
            document.querySelector('#rig').value = localDoc.rig || '';
            document.querySelector('#equipment').value = localDoc.equipment || '';
            document.querySelector('#status').value = localDoc.status || '';
            document.querySelector('#technician').value = localDoc.technician || '';
            document.querySelector('#timestamp').value = localDoc.timestamp || '';
        } catch (e) {
            // No local doc found; keep fields as-is
            console.warn('Local doc not found:', e);
        }
        <?php endif; ?>

        // If this page was submitted (POST) but cluster failed, persist changes locally
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rig'])): ?>
        const updated = {
            _id: id,
            // Fetch latest local _rev if exists, else allow PouchDB to create new
            ...(await localDB.get(id).catch(() => ({ _id: id }))),
            rig: <?php echo json_encode($_POST['rig']); ?>,
            equipment: <?php echo json_encode($_POST['equipment']); ?>,
            status: <?php echo json_encode($_POST['status']); ?>,
            technician: <?php echo json_encode($_POST['technician']); ?>,
            timestamp: <?php echo json_encode($_POST['timestamp']); ?>
        };
        // Ensure _rev is preserved if present
        if (updated._rev) {
            await localDB.put(updated);
        } else {
            // Create new local doc if it doesn't exist yet
            await localDB.put(updated);
        }
        document.querySelector('.message').textContent = "✅ Document updated locally. It will sync when CouchDB is reachable.";
        <?php endif; ?>
    } catch (err) {
        document.querySelector('.message').textContent = "❌ Offline update failed: " + err;
        console.error(err);
    }
})();
</script>
<?php endif; ?>
</body>
</html>
