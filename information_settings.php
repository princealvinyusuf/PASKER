<?php
// Database connection (copied from db.php, but using paskerid_db)
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'paskerid_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Initialize variables
$id = $title = $description = $date = $type = $subject = $file_url = $iframe_url = '';
$created_at = $updated_at = '';
$edit_mode = false;

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    $subject = $_POST['subject'];
    $file_url = $_POST['file_url'];
    $iframe_url = $_POST['iframe_url'];
    $created_at = $_POST['created_at'] ?? null;
    $updated_at = $_POST['updated_at'] ?? null;

    if (isset($_POST['save'])) {
        // Add new record
        $stmt = $conn->prepare("INSERT INTO information (title, description, date, type, subject, file_url, iframe_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssss', $title, $description, $date, $type, $subject, $file_url, $iframe_url, $created_at, $updated_at);
        $stmt->execute();
        $stmt->close();
        header('Location: information_settings.php');
        exit();
    } elseif (isset($_POST['update'])) {
        // Update record
        $stmt = $conn->prepare("UPDATE information SET title=?, description=?, date=?, type=?, subject=?, file_url=?, iframe_url=?, created_at=?, updated_at=? WHERE id=?");
        $stmt->bind_param('sssssssssi', $title, $description, $date, $type, $subject, $file_url, $iframe_url, $created_at, $updated_at, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: information_settings.php');
        exit();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM information WHERE id=$id");
    header('Location: information_settings.php');
    exit();
}

// Handle Edit (fetch data)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM information WHERE id=$id");
    if ($result && $row = $result->fetch_assoc()) {
        $title = $row['title'];
        $description = $row['description'];
        $date = $row['date'];
        $type = $row['type'];
        $subject = $row['subject'];
        $file_url = $row['file_url'];
        $iframe_url = $row['iframe_url'];
        $created_at = $row['created_at'];
        $updated_at = $row['updated_at'];
        $edit_mode = true;
    }
}

// Fetch all records
$records = $conn->query("SELECT * FROM information ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Information Settings</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        form { margin-bottom: 20px; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
    <h1>Information Settings</h1>
    <form method="post">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <?php endif; ?>
        <label>Title: <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required></label><br>
        <label>Description:<br><textarea name="description" rows="3" cols="50" required><?php echo htmlspecialchars($description); ?></textarea></label><br>
        <label>Date: <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required></label><br>
        <label>Type: <input type="text" name="type" value="<?php echo htmlspecialchars($type); ?>" required></label><br>
        <label>Subject: <input type="text" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required></label><br>
        <label>File URL: <input type="text" name="file_url" value="<?php echo htmlspecialchars($file_url); ?>"></label><br>
        <label>Iframe URL: <input type="text" name="iframe_url" value="<?php echo htmlspecialchars($iframe_url); ?>"></label><br>
        <label>Created At: <input type="datetime-local" name="created_at" value="<?php echo $created_at ? date('Y-m-d\TH:i', strtotime($created_at)) : ''; ?>"></label><br>
        <label>Updated At: <input type="datetime-local" name="updated_at" value="<?php echo $updated_at ? date('Y-m-d\TH:i', strtotime($updated_at)) : ''; ?>"></label><br>
        <?php if ($edit_mode): ?>
            <button type="submit" name="update">Update</button>
            <a href="information_settings.php">Cancel</a>
        <?php else: ?>
            <button type="submit" name="save">Add</button>
        <?php endif; ?>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Date</th>
            <th>Type</th>
            <th>Subject</th>
            <th>File URL</th>
            <th>Iframe URL</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        <?php if ($records && $records->num_rows > 0): ?>
            <?php while ($row = $records->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td><?php echo htmlspecialchars($row['file_url']); ?></td>
                    <td><?php echo htmlspecialchars($row['iframe_url']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['updated_at']; ?></td>
                    <td class="actions">
                        <a href="information_settings.php?edit=<?php echo $row['id']; ?>">Edit</a>
                        <a href="information_settings.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="11">No records found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?> 