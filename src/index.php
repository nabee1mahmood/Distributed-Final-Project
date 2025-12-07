<?php
// crud.php

// Include read.php to fetch CouchDB data
$items = include 'read.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD Dashboard</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    /* Reset & font */
    body {
        margin: 0;
        font-family: 'Inter', sans-serif;
        background-color: #f2f2f5;
        color: #1d1d1f;
    }
    h1 {
        text-align: center;
        font-weight: 700;
        margin-top: 60px;
        font-size: 2.2em;
        color: #1d1d1f;
    }

    /* Container */
    .container {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 40px;
    }

    /* Buttons */
    .button {
        display: inline-block;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        font-size: 0.95em;
        margin-top: 20px;
    }
    .create {
        background: #0071e3;
        color: white;
        margin-bottom: 30px;
    }
    .create:hover {
        background: #005bb5;
    }
    .update {
        background: #ff9500;
        color: white;
    }
    .update:hover {
        background: #cc7a00;
    }
    .delete {
        background: #ff3b30;
        color: white;
    }
    .delete:hover {
        background: #cc2a24;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }
    th, td {
        text-align: left;
        padding: 15px 20px;
    }
    th {
        font-weight: 600;
        background-color: #f7f7f8;
        color: #1d1d1f;
    }
    tr {
        border-bottom: 1px solid #e0e0e0;
    }
    tr:last-child {
        border-bottom: none;
    }

    /* Actions */
    td form {
        display: inline-block;
    }
</style>
</head>
<body>

<h1>CRUD Dashboard</h1>

<div class="container">
    <div style="text-align:center;">
        <form method="post" action="create.php">
            <button class="button create">Create New</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['email']); ?></td>
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
                <tr>
                    <td colspan="4" style="text-align:center; padding: 30px;">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
