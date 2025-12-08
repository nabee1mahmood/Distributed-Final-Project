<?php
// CouchDB connection info
$url = "http://admin:admin@192.168.49.2:30084/testdb";

$message = '';

// Get the document ID from POST
$id = $_POST['id'] ?? '';

if ($id) {
    // Get current revision of the document
    $ch = curl_init("$url/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200) {
        $doc = json_decode($response, true);
        $rev = $doc['_rev'];

        // Delete the document using its revision
        $ch = curl_init("$url/$id?rev=$rev");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $deleteResponse = curl_exec($ch);
        $deleteCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($deleteCode === 200) {
            $message = "✅ Document deleted successfully!";
            header("Refresh:1; url=crud.php");
            exit;
        } else {
            $message = "❌ Failed to delete document. Response: $deleteResponse";
        }
    } else {
        $message = "⚠ Document not found or error fetching document.";
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
<title>Delete Document</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #f2f2f5;
    color: #1d1d1f;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.card {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    max-width: 450px;
    width: 100%;
    text-align: center;
}
h2 {
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 1.8em;
    color: #1d1d1f;
}
.message {
    font-weight: 600;
    margin-bottom: 20px;
    font-size: 0.95em;
    color: #1d1d1f;
}
a {
    display: inline-block;
    margin-top: 20px;
    color: #0071e3;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95em;
}
a:hover { color: #005bb5; }
</style>
</head>
<body>

<div class="card">
    <h2>Delete Document</h2>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <a href="index.php">← Back to Dashboard</a>
</div>

</body>
</html>
