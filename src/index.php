<?php
// Include read.php to fetch CouchDB data
$items = include 'read.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oil Rig Maintenance Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Roboto',sans-serif; background:#121212; color:#e0e0e0; }
h1 { text-align:center; margin-top:50px; font-size:2.5em; color:#f2a900; letter-spacing:1px; }
.container { max-width:1100px; margin:40px auto; background:#1f1f1f; border-radius:16px; padding:30px 40px; box-shadow:0 10px 30px rgba(0,0,0,0.4); }
.button { display:inline-block; padding:10px 25px; border-radius:10px; border:none; font-weight:500; font-size:0.95em; cursor:pointer; transition:0.2s ease-in-out; margin:5px 5px; }
.create { background:#f2a900; color:#121212; } .create:hover { background:#d18e00; }
.update { background:#0071e3; color:#fff; } .update:hover { background:#005bb5; }
.delete { background:#ff3b30; color:#fff; } .delete:hover { background:#cc2a24; }
.cluster { background:#6c63ff; color:#fff; } .cluster:hover { background:#4b42cc; }
.pouch { background:#00c853; color:#fff; } .pouch:hover { background:#009624; }
table { width:100%; border-collapse:collapse; margin-top:20px; font-size:0.95em; color:#e0e0e0; }
th,td { text-align:left; padding:12px 15px; }
th { background:#2c2c2c; font-weight:600; }
tr { border-bottom:1px solid #333; }
tr:nth-child(even){ background:#1a1a1a; }
td form { display:inline-block; }
#pouchdb-status { margin-top:20px; padding:15px; background:#2c2c2c; border-radius:10px; font-size:0.9em; }
</style>
</head>
<body>

<h1>Oil Rig Maintenance Dashboard</h1>

<div class="container">
    <div style="text-align:center;">
        <form method="post" action="create.php" style="display:inline-block;">
            <button class="button create">Add Maintenance Record (Server)</button>
        </form>
        <form method="get" action="cluster.php" style="display:inline-block;">
            <button class="button cluster">View Cluster</button>
        </form>
        <form method="get" action="offlineinsert.php" style="display:inline-block;">
            <button class="button pouch">Add Record via PouchDB (Offline‑First)</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rig</th>
                <th>Equipment</th>
                <th>Status</th>
                <th>Technician</th>
                <th>Timestamp</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['rig']); ?></td>
                        <td><?php echo htmlspecialchars($item['equipment']); ?></td>
                        <td><?php echo htmlspecialchars($item['status']); ?></td>
                        <td><?php echo htmlspecialchars($item['technician']); ?></td>
                        <td><?php echo htmlspecialchars($item['timestamp']); ?></td>
                        <td>
                            <form method="post" action="update.php">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <button class="button update">Update</button>
                            </form>
                            <form method="post" action="delete.php" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <button class="button delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding:30px;">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="pouchdb-status">
        <strong>PouchDB Sync Status:</strong>
        <div id="sync-log">Initializing...</div>
        <div id="doc-count">Local documents: 0</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/pouchdb@9.0.0/dist/pouchdb.min.js"></script>
<script>
const localDB = new PouchDB('testdb');
const remoteDB = new PouchDB('http://admin:admin@localhost:8080/couchdb/testdb');

function updateDocCount() {
    localDB.info().then(info => {
        document.getElementById('doc-count').textContent = 'Local documents: ' + info.doc_count;
    });
}

localDB.sync(remoteDB, { live: true, retry: true })
    .on('change', info => { document.getElementById('sync-log').textContent = 'Change: ' + JSON.stringify(info); updateDocCount(); })
    .on('paused', () => { document.getElementById('sync-log').textContent = 'Replication paused'; })
    .on('active', () => { document.getElementById('sync-log').textContent = 'Replication resumed'; })
    .on('error', err => { document.getElementById('sync-log').textContent = 'Sync error: ' + err; });

updateDocCount();
</script>

</body>
</html>
