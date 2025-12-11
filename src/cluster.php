<?php
// CouchDB cluster connection info
$couchHosts = ["couch1", "couch2", "couch3"]; // service names from docker-compose.yml
$couchPort  = "5984";
$dbName     = "testdb";

$nodesInfo = [];

foreach ($couchHosts as $host) {
    $nodeName = $host;
    $docCount = null;

    // Query database info for this node
    $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['doc_count'])) {
            $docCount = $data['doc_count'];
        }
    }

    $nodesInfo[] = [
        'name' => $nodeName,
        'doc_count' => $docCount
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CouchDB Cluster</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #121212;
    color: #e0e0e0;
}
h1 {
    text-align: center;
    margin-top: 50px;
    font-size: 2.5em;
    color: #f2a900;
    letter-spacing: 1px;
}
.container {
    max-width: 800px;
    margin: 40px auto;
    background-color: #1f1f1f;
    border-radius: 16px;
    padding: 30px 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    text-align: center;
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
    margin: 10px 0;
}
.back { background-color: #0071e3; color: #fff; }
.back:hover { background-color: #005bb5; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 0.95em;
    color: #e0e0e0;
}
th, td {
    text-align: left;
    padding: 12px 15px;
}
th {
    background-color: #2c2c2c;
    font-weight: 600;
}
tr {
    border-bottom: 1px solid #333;
}
tr:nth-child(even) { background-color: #1a1a1a; }
</style>
</head>
<body>

<h1>CouchDB Cluster Nodes</h1>

<div class="container">
    <?php if (!empty($nodesInfo)): ?>
        <table>
            <thead>
                <tr>
                    <th>Node</th>
                    <th>Document Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nodesInfo as $node): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($node['name']); ?></td>
                        <td>
                            <?php 
                            echo $node['doc_count'] !== null 
                                ? htmlspecialchars($node['doc_count']) 
                                : "⚠ Unreachable";
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding:20px;">⚠ Unable to fetch cluster nodes.</p>
    <?php endif; ?>

    <form method="get" action="index.php">
        <button class="button back">← Back to Dashboard</button>
    </form>
</div>

</body>
</html>
