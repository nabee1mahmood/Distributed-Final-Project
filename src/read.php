<?php
// CouchDB cluster connection info
$couchHosts = ["couch1", "couch2", "couch3"]; // service names from docker-compose.yml
$couchPort  = "5984";
$dbName     = "testdb";

$items = [];
$response = false;

// Try each CouchDB host until one responds
foreach ($couchHosts as $host) {
    $url = "http://admin:admin@{$host}:{$couchPort}/{$dbName}/_all_docs?include_docs=true";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);

    if ($response !== false) {
        // Successfully got a response, break out of the loop
        break;
    }
}

if ($response !== false) {
    $data = json_decode($response, true);
    if (!empty($data['rows'])) {
        foreach ($data['rows'] as $row) {
            if (!empty($row['doc'])) {
                $doc = $row['doc'];
                $items[] = [
                    'id'         => $doc['_id'] ?? '',
                    'rig'        => $doc['rig'] ?? '',
                    'equipment'  => $doc['equipment'] ?? '',
                    'status'     => $doc['status'] ?? '',
                    'technician' => $doc['technician'] ?? '',
                    'notes'      => $doc['notes'] ?? '',
                    'timestamp'  => $doc['timestamp'] ?? ''
                ];
            }
        }
    }

    // Online mode
    return [
        'offline' => false,
        'docs'    => $items
    ];
}

// Offline mode
return [
    'offline' => true,
    'docs'    => []
];
