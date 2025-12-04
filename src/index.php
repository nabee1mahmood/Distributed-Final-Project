<?php
// Include read.php, which returns an array of items
$items = include __DIR__ . '/read.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Front Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        h1 { text-align: center; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; background-color: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #007BFF; color: white; }
        button { padding: 5px 10px; margin: 2px; border: none; border-radius: 4px; cursor: pointer; color: white; }
        .create { background-color: #28a745; }
        .update { background-color: #ffc107; color: black; }
        .delete { background-color: #dc3545; }
        form { display: inline; }
    </style>
</head>
<body>

<h1>CRUD Front Page</h1>

<div style="text-align:center; margin-bottom:20px;">
    <form method="post" action="create.php">
        <button class="create">Create New</button>
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
                        <form method="post" action="update.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button class="update">Update</button>
                        </form>
                        <form method="post" action="delete.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button class="delete" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No data found or connection error.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
