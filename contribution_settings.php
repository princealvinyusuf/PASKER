<?php
// Contribution Settings - CRUD for 'contributions' table in paskerid_db
// Connect to paskerid_db (reuse db.php logic, but override db name)
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'paskerid_db';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle Add
if (isset($_POST['add'])) {
    $icon = $conn->real_escape_string($_POST['icon']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO contributions (icon, title, description, created_at, updated_at) VALUES ('$icon', '$title', '$description', '$now', '$now')";
    $conn->query($sql);
    header('Location: contribution_settings.php');
    exit();
}
// Handle Edit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM contributions WHERE id=$edit_id");
    $edit_contribution = $edit_result->fetch_assoc();
}
// Handle Update
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $icon = $conn->real_escape_string($_POST['icon']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE contributions SET icon='$icon', title='$title', description='$description', updated_at='$now' WHERE id=$id";
    $conn->query($sql);
    header('Location: contribution_settings.php');
    exit();
}
// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM contributions WHERE id=$delete_id");
    header('Location: contribution_settings.php');
    exit();
}
// Fetch all contributions
$contributions = $conn->query("SELECT * FROM contributions ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; }
        .container { max-width: 900px; margin-top: 40px; }
        table { background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { vertical-align: middle; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4">Contribution Settings</h1>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><?php echo isset($edit_contribution) ? 'Edit Contribution' : 'Add New Contribution'; ?></h5>
            <form method="post">
                <?php if (isset($edit_contribution)): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_contribution['id']; ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Icon (FontAwesome)</label>
                        <input type="text" name="icon" class="form-control" required value="<?php echo isset($edit_contribution) ? htmlspecialchars($edit_contribution['icon']) : ''; ?>" placeholder="e.g. fa-users">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required value="<?php echo isset($edit_contribution) ? htmlspecialchars($edit_contribution['title']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" required value="<?php echo isset($edit_contribution) ? htmlspecialchars($edit_contribution['description']) : ''; ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="<?php echo isset($edit_contribution) ? 'update' : 'add'; ?>" class="btn btn-primary">
                        <?php echo isset($edit_contribution) ? 'Update' : 'Add'; ?>
                    </button>
                    <?php if (isset($edit_contribution)): ?>
                        <a href="contribution_settings.php" class="btn btn-secondary ms-2">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <h4>All Contributions</h4>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mt-2">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $contributions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><i class="fa <?php echo htmlspecialchars($row['icon']); ?>"></i> <?php echo htmlspecialchars($row['icon']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['updated_at']; ?></td>
                    <td class="actions">
                        <a href="contribution_settings.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="contribution_settings.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this contribution?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- FontAwesome CDN for icons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html> 