<?php
$url = "http://admin:admin@192.168.49.2:30084/testdb/_all_docs?include_docs=true";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);

$items = [];

if ($response !== false) {
    $data = json_decode($response, true);
    if (!empty($data['rows'])) {
        foreach ($data['rows'] as $row) {
            $doc = $row['doc'];
            $items[] = [
                'id'    => $doc['_id'],
                'name'  => $doc['name'] ?? '',
                'email' => $doc['email'] ?? ''
            ];
        }
    }
}

return $items;
