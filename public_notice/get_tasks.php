<?php
// ১. যেকোনো লুকানো স্পেস বা BOM ক্যারেক্টার মুছে ফেলা
ob_start();

header('Content-Type: application/json; charset=utf-8');

// ২. ডাটাবেজ ফাইলের পাথ চেক ও ইনক্লুড
$db_path = __DIR__ . '/../config/database.php';

if (!file_exists($db_path)) {
    ob_end_clean();
    echo json_encode([
        'tabs' => '<p class="text-danger text-center py-3">config/database.php ফাইল পাওয়া যায়নি!</p>',
        'contents' => ''
    ]);
    exit();
}

require_once($db_path);

// ৩. PDO কানেকশন চেক
if (!isset($conn) || !($conn instanceof PDO)) {
    ob_end_clean();
    echo json_encode([
        'tabs' => '<p class="text-danger text-center py-3">ডাটাবেজ কানেকশন করতে ব্যর্থ হয়েছে!</p>',
        'contents' => ''
    ]);
    exit();
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

try {
    // ৪. PDO এর মাধ্যমে ডাটা ফেচ করা
    $stmt = $conn->prepare("SELECT * FROM practical_tasks WHERE subject_id = :subject_id ORDER BY id DESC");
    $stmt->execute([':subject_id' => $subject_id]);
    $tasks = $stmt->fetchAll();

    // ৫. যদি কোনো ডাটা না থাকে
    if (empty($tasks)) {
        ob_end_clean();
        echo json_encode([
            'tabs' => '<div class="text-center py-4"><p class="text-muted mb-0" style="color: #94a3b8 !important;">এই বিষয়ে কোনো টাস্ক দেওয়া হয় নি।</p></div>',
            'contents' => ''
        ]);
        exit();
    }

    // ৬. Type অনুযায়ী গ্রুপ করা (Job, Sessional, Presentation, Assignment)
    $groupedTasks = [];
    foreach ($tasks as $t) {
        $type = !empty($t['task_type']) ? $t['task_type'] : 'Job';
        $groupedTasks[$type][] = $t;
    }

    $tabsHtml = '<ul class="nav nav-pills mb-3 justify-content-center gap-2 flex-wrap" role="tablist">';
    $contentsHtml = '<div class="tab-content">';

    $isFirst = true;
    foreach ($groupedTasks as $type => $taskList) {
        $safeType = preg_replace('/[^a-zA-Z0-9]/', '', $type);
        $activeClass = $isFirst ? 'active' : '';
        $showActiveClass = $isFirst ? 'show active' : '';

        // ডার্ক থিমের সাথে মানানসই পিল বাটন
        $tabsHtml .= '<li class="nav-item" role="presentation">';
        $tabsHtml .= '<button class="nav-link ' . $activeClass . ' fw-bold btn-sm rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tab-' . $safeType . '" type="button">' . htmlspecialchars($type) . ' (' . count($taskList) . ')</button>';
        $tabsHtml .= '</li>';

        $contentsHtml .= '<div class="tab-pane fade ' . $showActiveClass . '" id="tab-' . $safeType . '">';
        foreach ($taskList as $item) {
            // bg-light এর বদলে ডার্ক সারফেস কার্ড ও সফট বর্ডার যুক্ত করা হয়েছে
            $contentsHtml .= '<div class="card border-0 mb-3 p-3 text-start shadow-sm rounded-3" style="background: rgba(17, 24, 39, 0.9) !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-left: 3px solid #2563eb !important;">';
            $contentsHtml .= '<h6 class="fw-bold mb-2" style="color: #60a5fa !important;">' . htmlspecialchars($item['title']) . '</h6>';
            if (!empty($item['description'])) {
                $contentsHtml .= '<p class="mb-0 small" style="color: #cbd5e1 !important; line-height: 1.6;">' . nl2br(htmlspecialchars($item['description'])) . '</p>';
            }
            $contentsHtml .= '</div>';
        }
        $contentsHtml .= '</div>';

        $isFirst = false;
    }

    $tabsHtml .= '</ul>';
    $contentsHtml .= '</div>';

    // ৭. বাফার ক্লিন করে শুধুমাত্র বিশুদ্ধ JSON আউটপুট দেওয়া
    ob_end_clean();
    echo json_encode([
        'tabs' => $tabsHtml,
        'contents' => $contentsHtml
    ]);

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode([
        'tabs' => '<p class="text-danger text-center py-3">Query Error: ' . htmlspecialchars($e->getMessage()) . '</p>',
        'contents' => ''
    ]);
}