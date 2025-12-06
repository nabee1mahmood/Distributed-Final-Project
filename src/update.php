
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($name && $email) {
        $updatedDoc = json_encode([
            "_id"  => $id,
            "_rev" => $rev,
            "name" => $name,
            "email" => $email
        ]);

        $ch = curl_init("$url/$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updatedDoc);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $updateResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 201 || $httpCode === 200) {
            header("Location: index.php");
            exit;
        } else {
            $message = "❌ Update failed: $updateResponse";
        }
    } else {
        $message = "⚠ Name and Email cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Document</title>
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
        width: 400px;
        text-align: center;
    }
    input {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        border-radius: 10px;
        border: 1px solid #ccc;
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
    button:hover {
        background: #cc7a00;
    }
    a {
        display: block;
        margin-top: 20px;
        color: #0071e3;
        font-weight: 600;
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Update Document</h2>

    <?php if ($doc): ?>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

            <input type="text" name="name" value="<?php echo htmlspecialchars($doc['name']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doc['email']); ?>" required>

            <button type="submit" name="update">Update</button>
        </form>
    <?php else: ?>
        <p>⚠ Unable to load document.</p>
    <?php endif; ?>

    <a href="index.php">← Back to Dashboard</a>
</div>

</body>
</html>



