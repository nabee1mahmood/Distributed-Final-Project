<?php
// CouchDB connection info
$url = "http://admin:admin@192.168.49.2:30084/testdb";

$message = '';
$success = false;

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
            $success = true;
            header("Refresh:2; url=index.php"); // 2s delay for animation
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
    max-width: 450px;
    width: 100%;
    text-align: center;
    animation: fadeInScale 0.6s ease forwards;
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
    color: <?php echo $success ? '#4caf50' : '#ff3b30'; ?>;
    opacity: 0;
    animation: fadeIn 1s ease forwards;
    animation-delay: 0.3s;
}

a {
    display: inline-block;
    margin-top: 20px;
    color: #0071e3;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95em;
    transition: color 0.2s ease-in-out;
}
a:hover { color: #005bb5; }

@keyframes fadeInScale {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
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
