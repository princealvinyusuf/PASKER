<?php
// Database connection (same as db.php, but with paskerid_db)
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'paskerid_db';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle Create
if (isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $chart_type = $conn->real_escape_string($_POST['chart_type']);
    $data_json = $conn->real_escape_string($_POST['data_json']);
    $order = intval($_POST['order']);
    $sql = "INSERT INTO charts (title, description, chart_type, data_json, `order`) VALUES ('$title', '$description', '$chart_type', '$data_json', $order)";
    $conn->query($sql);
    header('Location: chart_settings.php');
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $chart_type = $conn->real_escape_string($_POST['chart_type']);
    $data_json = $conn->real_escape_string($_POST['data_json']);
    $order = intval($_POST['order']);
    $sql = "UPDATE charts SET title='$title', description='$description', chart_type='$chart_type', data_json='$data_json', `order`=$order WHERE id=$id";
    $conn->query($sql);
    header('Location: chart_settings.php');
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM charts WHERE id=$id");
    header('Location: chart_settings.php');
    exit();
}

// Fetch all charts
$result = $conn->query("SELECT * FROM charts ORDER BY `order`");

// Fetch single chart for editing
$edit_chart = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM charts WHERE id=$id");
    $edit_chart = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart Settings</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #eee; }
        form { margin-bottom: 20px; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
    <h1>Chart Settings</h1>
    <h2><?php echo $edit_chart ? 'Edit Chart' : 'Add New Chart'; ?></h2>
    <form method="post">
        <?php if ($edit_chart): ?>
            <input type="hidden" name="id" value="<?php echo $edit_chart['id']; ?>">
        <?php endif; ?>
        <label>Title:<br><input type="text" name="title" required value="<?php echo $edit_chart ? htmlspecialchars($edit_chart['title']) : ''; ?>"></label><br>
        <label>Description:<br><textarea name="description" required><?php echo $edit_chart ? htmlspecialchars($edit_chart['description']) : ''; ?></textarea></label><br>
        <label>Chart Type:<br><input type="text" name="chart_type" required value="<?php echo $edit_chart ? htmlspecialchars($edit_chart['chart_type']) : ''; ?>"></label><br>
        <label>Data JSON:<br><textarea name="data_json" required><?php echo $edit_chart ? htmlspecialchars($edit_chart['data_json']) : ''; ?></textarea></label><br>
        <label>Order:<br><input type="number" name="order" required value="<?php echo $edit_chart ? intval($edit_chart['order']) : 0; ?>"></label><br><br>
        <button type="submit" name="<?php echo $edit_chart ? 'update' : 'add'; ?>"><?php echo $edit_chart ? 'Update' : 'Add'; ?></button>
        <?php if ($edit_chart): ?>
            <a href="chart_settings.php">Cancel</a>
        <?php endif; ?>
    </form>
    <h2>All Charts</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Chart Type</th>
            <th>Data JSON</th>
            <th>Order</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['chart_type']); ?></td>
            <td><textarea readonly style="width:150px;height:40px;"><?php echo htmlspecialchars($row['data_json']); ?></textarea></td>
            <td><?php echo $row['order']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo $row['updated_at']; ?></td>
            <td class="actions">
                <a href="chart_settings.php?edit=<?php echo $row['id']; ?>">Edit</a>
                <a href="chart_settings.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this chart?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html> 