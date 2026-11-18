<?php
require_once("../config/session.php");
require_once("../config/database.php");

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$subStmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
$subStmt->execute([$subject_id]);
$subject = $subStmt->fetch();

if(!$subject){
    header("Location: index.php");
    exit();
}

// --- ১. Add Task Logic ---
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_task'){
    $task_type   = $_POST['task_type'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);

    if(!empty($title)){
        $stmt = $conn->prepare("INSERT INTO practical_tasks (subject_id, task_type, title, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$subject_id, $task_type, $title, $description]);
        header("Location: manage_tasks.php?subject_id=" . $subject_id . "&success=1");
        exit();
    }
}

// --- ২. Edit Task Logic ---
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_task'){
    $task_id     = (int)$_POST['task_id'];
    $task_type   = $_POST['task_type'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);

    if(!empty($title) && $task_id > 0){
        $stmt = $conn->prepare("UPDATE practical_tasks SET task_type = ?, title = ?, description = ? WHERE id = ? AND subject_id = ?");
        $stmt->execute([$task_type, $title, $description, $task_id, $subject_id]);
        header("Location: manage_tasks.php?subject_id=" . $subject_id . "&updated=1");
        exit();
    }
}

$taskStmt = $conn->prepare("SELECT * FROM practical_tasks WHERE subject_id = ? ORDER BY id DESC");
$taskStmt->execute([$subject_id]);
$tasks = $taskStmt->fetchAll();

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">
        <i class="fa-solid fa-list-check text-primary me-2"></i> <?= htmlspecialchars($subject['subject_code']) ?> - Tasks Management
    </h3>
    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm" style="border-radius:15px;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">Add New Job / Sessional Task</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_task">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Task Type</label>
                        <select name="task_type" class="form-select" required>
                            <option value="Job">Job</option>
                            <option value="Sessional">Sessional</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Presentation">Presentation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter Task Title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter task details..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-plus me-1"></i> Add Task</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm" style="border-radius:15px;">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold text-dark">Existing Tasks</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th width="110">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($tasks) > 0): ?>
                                <?php foreach($tasks as $t): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $t['task_type'] == 'Job' ? 'bg-primary' : 'bg-info text-dark' ?>">
                                            <?= $t['task_type'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($t['title']) ?></td>
                                    <td>
                                        <!-- Edit Button (Modal Trigger) -->
                                        <button class="btn btn-sm btn-warning me-1 text-white" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTaskModal" 
                                                onclick='openEditModal(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <a href="delete_task.php?id=<?= $t['id'] ?>&subject_id=<?= $subject_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">No tasks added for this subject.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Task -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;">
            <div class="modal-header bg-warning text-white">
                <h6 class="modal-title fw-bold" id="editTaskModalLabel"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Task</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_task">
                    <input type="hidden" name="task_id" id="edit_task_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Task Type</label>
                        <select name="task_type" id="edit_task_type" class="form-select" required>
                            <option value="Job">Job</option>
                            <option value="Sessional">Sessional</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Presentation">Presentation</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" placeholder="Enter Task Title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4" placeholder="Enter task details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-white"><i class="fa-solid fa-check me-1"></i> Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(task) {
    document.getElementById('edit_task_id').value = task.id;
    document.getElementById('edit_task_type').value = task.task_type;
    document.getElementById('edit_title').value = task.title;
    document.getElementById('edit_description').value = task.description ? task.description : '';
}
</script>

<?php include("../includes/footer.php"); ?>