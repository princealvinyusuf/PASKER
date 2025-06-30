<?php
// Use the same DB connection method as db.php, but connect to 'paskerid_db'
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
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];
    $date = $_POST['date'];
    $author = $_POST['author'];
    $stmt = $conn->prepare("INSERT INTO news (title, content, image_url, date, author) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $content, $image_url, $date, $author);
    $stmt->execute();
    $stmt->close();
    header("Location: news_settings.php");
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];
    $date = $_POST['date'];
    $author = $_POST['author'];
    $stmt = $conn->prepare("UPDATE news SET title=?, content=?, image_url=?, date=?, author=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $content, $image_url, $date, $author, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: news_settings.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM news WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: news_settings.php");
    exit();
}

// Handle Edit (fetch data)
$edit_news = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM news WHERE id=$id");
    $edit_news = $result->fetch_assoc();
}

// Fetch all news
$news = $conn->query("SELECT * FROM news ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News Settings</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        form { margin-bottom: 20px; }
        input, textarea { width: 100%; }
    </style>
</head>
<body>
    <h2>News Settings</h2>
    <h3><?php echo $edit_news ? 'Edit News' : 'Add News'; ?></h3>
    <form method="post">
        <?php if ($edit_news): ?>
            <input type="hidden" name="id" value="<?php echo $edit_news['id']; ?>">
        <?php endif; ?>
        <label>Title:<br><input type="text" name="title" required value="<?php echo $edit_news['title'] ?? ''; ?>"></label><br><br>
        <label>Content:<br><textarea name="content" required><?php echo $edit_news['content'] ?? ''; ?></textarea></label><br><br>
        <label>Image URL:<br><input type="text" name="image_url" value="<?php echo $edit_news['image_url'] ?? ''; ?>"></label><br><br>
        <label>Date:<br><input type="date" name="date" required value="<?php echo $edit_news['date'] ?? ''; ?>"></label><br><br>
        <label>Author:<br><input type="text" name="author" value="<?php echo $edit_news['author'] ?? ''; ?>"></label><br><br>
        <button type="submit" name="<?php echo $edit_news ? 'update' : 'add'; ?>"><?php echo $edit_news ? 'Update' : 'Add'; ?></button>
        <?php if ($edit_news): ?>
            <a href="news_settings.php">Cancel</a>
        <?php endif; ?>
    </form>
    <h3>All News</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Image URL</th>
            <th>Date</th>
            <th>Author</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $news->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['content'])); ?></td>
            <td><?php echo htmlspecialchars($row['image_url']); ?></td>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo $row['updated_at']; ?></td>
            <td>
                <a href="news_settings.php?edit=<?php echo $row['id']; ?>">Edit</a> |
                <a href="news_settings.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this news?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?> 