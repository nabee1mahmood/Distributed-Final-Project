<?php
// CouchDB connection info
$url = "http://admin:admin@192.168.49.2:30084/testdb"; // DB URL

$message = ''; // Message to display after submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($name && $email) {
        $data = json_encode([
            'name'  => $name,
            'email' => $email
        ]);

        $ch = curl_init("$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode === 201) {
            $message = "✅ Document created successfully!";
            header("Refresh:1; url=index.php");
        } else {
            $message = "❌ Failed to create document. Response: " . $response;
        }
    } else {
        $message = "⚠ Name and Email are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create New Document</title>
<!-- Google Fonts -->
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

    input[type=text], input[type=email] {
        width: 100%;
        padding: 12px 15px;
        margin: 10px 0 20px 0;
        border-radius: 12px;
        border: 1px solid #ccc;
        font-size: 0.95em;
        transition: all 0.2s ease-in-out;
    }

    input[type=text]:focus, input[type=email]:focus {
        border-color: #0071e3;
        outline: none;
        box-shadow: 0 0 5px rgba(0,113,227,0.3);
    }

    button {
        width: 100%;
        padding: 12px;
        font-weight: 600;
        font-size: 1em;
        border-radius: 12px;
        border: none;
        background: #0071e3;
        color: #fff;
        cursor: pointer;
        transition: background 0.2s ease-in-out;
    }

    button:hover {
        background: #005bb5;
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

    a:hover {
        color: #005bb5;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Create New Document</h2>

    <?php if($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Create</button>
    </form>

    <a href="index.php">← Back to Dashboard</a>
</div>

</body>
</html>
