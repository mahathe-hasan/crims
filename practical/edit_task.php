<?php
ob_start();
session_start();

// ডাটাবেজ ইনক্লুড
$db_path = __DIR__ . '/../config/database.php';
if (!file_exists($db_path)) {
    die("Database configuration file missing!");
}
require_once($db_path);

$message = "";
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id <= 0) {
    die("Invalid Task ID!");
}

// ১. ডাটাবেজ থেকে বর্তমান টাস্কের ডাটা ফেচ করা
try {
    $stmt = $conn->prepare("SELECT * FROM practical_tasks WHERE id = :id");
    $stmt->execute([':id' => $task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        die("Task not found!");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// ২. ফর্ম সাবমিট হলে ডাটা আপডেট করা
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_type   = trim($_POST['task_type']);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (!empty($title) && !empty($task_type)) {
        try {
            $updateStmt = $conn->prepare("UPDATE practical_tasks SET task_type = :task_type, title = :title, description = :description WHERE id = :id");
            $updateStmt->execute([
                ':task_type'   => $task_type,
                ':title'       => $title,
                ':description' => $description,
                ':id'          => $task_id
            ]);

            $message = '<div class="alert alert-success">টাস্ক সফলভাবে আপডেট করা হয়েছে!</div>';
            
            // ডাটাবেজ থেকে আপডেট ডাটা রিলোড করা
            $stmt->execute([':id' => $task_id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Task Type এবং Title ঘর পূরণ করা বাধ্যতামূলক!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Practical Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Practical Task</h5>
                    <a href="manage_tasks.php" class="btn btn-sm btn-light">Back to Tasks</a>
                </div>
                <div class="card-body p-4">
                    <?= $message; ?>

                    <form action="" method="POST">
                        <!-- Task Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Task Type</label>
                            <select name="task_type" class="form-select" required>
                                <option value="Job" <?= ($task['task_type'] == 'Job') ? 'selected' : ''; ?>>Job</option>
                                <option value="Sessional" <?= ($task['task_type'] == 'Sessional') ? 'selected' : ''; ?>>Sessional</option>
                                <option value="Presentation" <?= ($task['task_type'] == 'Presentation') ? 'selected' : ''; ?>>Presentation</option>
                                <option value="Assignment" <?= ($task['task_type'] == 'Assignment') ? 'selected' : ''; ?>>Assignment</option>
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']); ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($task['description']); ?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>