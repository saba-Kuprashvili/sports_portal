<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// წაშლა
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // ქვიზის კითხვები და შედეგები ავტომატურად იშლება CASCADE-ით
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    if ($stmt->execute([$id])) {
        show_message('ქვიზი წარმატებით წაიშალა', 'success');
    }
    header('Location: quizzes.php');
    exit;
}

// ქვიზის დამატება/რედაქტირება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $category = clean_input($_POST['category']);
    $difficulty = clean_input($_POST['difficulty']);
    $time_limit = intval($_POST['time_limit']);
    
    if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
        // რედაქტირება
        $id = $_POST['edit_id'];
        $stmt = $pdo->prepare("
            UPDATE quizzes 
            SET title = ?, description = ?, category = ?, difficulty = ?, time_limit = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $category, $difficulty, $time_limit, $id]);
        show_message('ქვიზი წარმატებით განახლდა', 'success');
    } else {
        // დამატება
        $stmt = $pdo->prepare("
            INSERT INTO quizzes (user_id, title, description, category, difficulty, time_limit) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $difficulty, $time_limit]);
        show_message('ქვიზი წარმატებით დაემატა', 'success');
    }
    
    header('Location: quizzes.php');
    exit;
}

// კითხვის დამატება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $question = clean_input($_POST['question']);
    $option_a = clean_input($_POST['option_a']);
    $option_b = clean_input($_POST['option_b']);
    $option_c = clean_input($_POST['option_c']);
    $option_d = clean_input($_POST['option_d']);
    $correct_answer = clean_input($_POST['correct_answer']);
    $points = intval($_POST['points']);
    
    $stmt = $pdo->prepare("
        INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, points) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $points]);
    
    show_message('კითხვა წარმატებით დაემატა', 'success');
    header("Location: quizzes.php?manage=$quiz_id");
    exit;
}

// კითხვის წაშლა
if (isset($_GET['delete_question']) && is_numeric($_GET['delete_question'])) {
    $question_id = $_GET['delete_question'];
    $quiz_id = $_GET['quiz_id'];
    
    $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE id = ?");
    $stmt->execute([$question_id]);
    
    show_message('კითხვა წაიშალა', 'success');
    header("Location: quizzes.php?manage=$quiz_id");
    exit;
}

// რედაქტირებისთვის მონაცემების წამოღება
$edit_quiz = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_quiz = $stmt->fetch();
}

// კითხვების მართვა
$manage_quiz = null;
$quiz_questions = [];
if (isset($_GET['manage']) && is_numeric($_GET['manage'])) {
    $quiz_id = $_GET['manage'];
    
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $manage_quiz = $stmt->fetch();
    
    if ($manage_quiz) {
        $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
        $stmt->execute([$quiz_id]);
        $quiz_questions = $stmt->fetchAll();
    }
}

// ყველა ქვიზის წამოღება
$stmt = $pdo->query("
    SELECT q.*, u.username, u.full_name,
    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
    (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id) as attempts
    FROM quizzes q
    JOIN users u ON q.user_id = u.id
    ORDER BY q.created_at DESC
");
$quizzes = $stmt->fetchAll();

$page_title = 'ქვიზების მართვა';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../css/admin_style.css">
<script>document.body.classList.add('admin-page');</script>

<div class="container">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h3><i class="fas fa-cog"></i> ადმინისტრირება</h3>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-chart-line"></i> დაშბორდი</a>
                <a href="articles.php"><i class="fas fa-newspaper"></i> სტატიები</a>
                <a href="news.php"><i class="fas fa-rss"></i> ახალი ამბები</a>
                <a href="quizzes.php" class="active"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <?php if ($manage_quiz): ?>
            <!-- კითხვების მართვა -->
            <div class="admin-header">
                <h1><i class="fas fa-question"></i> კითხვების მართვა: <?php echo clean_input($manage_quiz['title']); ?></h1>
                <a href="quizzes.php" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i> უკან
                </a>
            </div>
            
            <!-- კითხვის დამატების ფორმა -->
            <div class="admin-form-container">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="add_question" value="1">
                    <input type="hidden" name="quiz_id" value="<?php echo $manage_quiz['id']; ?>">
                    <h2>ახალი კითხვის დამატება</h2>
                    
                    <div class="form-group">
                        <label><i class="fas fa-question"></i> კითხვა *</label>
                        <textarea name="question" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>A) პასუხი *</label>
                            <input type="text" name="option_a" required>
                        </div>
                        <div class="form-group">
                            <label>B) პასუხი *</label>
                            <input type="text" name="option_b" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>C) პასუხი *</label>
                            <input type="text" name="option_c" required>
                        </div>
                        <div class="form-group">
                            <label>D) პასუხი *</label>
                            <input type="text" name="option_d" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-check"></i> სწორი პასუხი *</label>
                            <select name="correct_answer" required>
                                <option value="">აირჩიეთ</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-star"></i> ქულა</label>
                            <input type="number" name="points" value="1" min="1" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="admin-btn">
                        <i class="fas fa-plus"></i> კითხვის დამატება
                    </button>
                </form>
            </div>
            
            <!-- კითხვების სია -->
            <div class="admin-section">
                <h2>კითხვები (<?php echo count($quiz_questions); ?>)</h2>
                <?php if (empty($quiz_questions)): ?>
                <p class="no-data">ჯერ არ არის დამატებული კითხვები</p>
                <?php else: ?>
                <div class="questions-list">
                    <?php foreach ($quiz_questions as $index => $q): ?>
                    <div class="question-item">
                        <div class="question-header">
                            <span class="question-num">კითხვა #<?php echo $index + 1; ?></span>
                            <span class="question-points"><?php echo $q['points']; ?> ქულა</span>
                        </div>
                        <h4><?php echo clean_input($q['question']); ?></h4>
                        <div class="question-options">
                            <div class="option <?php echo $q['correct_answer'] === 'A' ? 'correct' : ''; ?>">
                                A) <?php echo clean_input($q['option_a']); ?>
                            </div>
                            <div class="option <?php echo $q['correct_answer'] === 'B' ? 'correct' : ''; ?>">
                                B) <?php echo clean_input($q['option_b']); ?>
                            </div>
                            <div class="option <?php echo $q['correct_answer'] === 'C' ? 'correct' : ''; ?>">
                                C) <?php echo clean_input($q['option_c']); ?>
                            </div>
                            <div class="option <?php echo $q['correct_answer'] === 'D' ? 'correct' : ''; ?>">
                                D) <?php echo clean_input($q['option_d']); ?>
                            </div>
                        </div>
                        <a href="quizzes.php?delete_question=<?php echo $q['id']; ?>&quiz_id=<?php echo $manage_quiz['id']; ?>" 
                           class="delete-question-btn"
                           onclick="return confirm('დარწმუნებული ხართ?');">
                            <i class="fas fa-trash"></i> წაშლა
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php else: ?>
            <!-- ქვიზების სია -->
            <div class="admin-header">
                <h1><i class="fas fa-question-circle"></i> ქვიზების მართვა</h1>
                <button onclick="toggleForm()" class="admin-btn">
                    <i class="fas fa-plus"></i> ახალი ქვიზი
                </button>
            </div>
            
            <!-- ფორმა -->
            <div id="quizForm" class="admin-form-container" style="display: <?php echo $edit_quiz ? 'block' : 'none'; ?>;">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="save_quiz" value="1">
                    <?php if ($edit_quiz): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_quiz['id']; ?>">
                    <h2>ქვიზის რედაქტირება</h2>
                    <?php else: ?>
                    <h2>ახალი ქვიზი</h2>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> სათაური *</label>
                        <input type="text" name="title" required 
                               value="<?php echo $edit_quiz ? clean_input($edit_quiz['title']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> აღწერა</label>
                        <textarea name="description" rows="3"><?php echo $edit_quiz ? clean_input($edit_quiz['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> კატეგორია</label>
                            <input type="text" name="category" 
                                   value="<?php echo $edit_quiz ? clean_input($edit_quiz['category']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-signal"></i> სირთულე *</label>
                            <select name="difficulty" required>
                                <option value="მარტივი" <?php echo ($edit_quiz && $edit_quiz['difficulty'] === 'მარტივი') ? 'selected' : ''; ?>>მარტივი</option>
                                <option value="საშუალო" <?php echo ($edit_quiz && $edit_quiz['difficulty'] === 'საშუალო') ? 'selected' : ''; ?>>საშუალო</option>
                                <option value="რთული" <?php echo ($edit_quiz && $edit_quiz['difficulty'] === 'რთული') ? 'selected' : ''; ?>>რთული</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> დროის ლიმიტი (წუთი, 0 = უსასრულო)</label>
                        <input type="number" name="time_limit" min="0" 
                               value="<?php echo $edit_quiz ? $edit_quiz['time_limit'] : 0; ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="admin-btn">
                            <i class="fas fa-save"></i> შენახვა
                        </button>
                        <button type="button" onclick="toggleForm()" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-times"></i> გაუქმება
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- ქვიზების სია -->
            <div class="admin-section">
                <h2>ყველა ქვიზი (<?php echo count($quizzes); ?>)</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>სათაური</th>
                                <th>კატეგორია</th>
                                <th>სირთულე</th>
                                <th>კითხვები</th>
                                <th>მცდელობები</th>
                                <th>ავტორი</th>
                                <th>თარიღი</th>
                                <th>მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $quiz): ?>
                            <tr>
                                <td>#<?php echo $quiz['id']; ?></td>
                                <td><?php echo clean_input(truncate_text($quiz['title'], 40)); ?></td>
                                <td><?php echo clean_input($quiz['category']); ?></td>
                                <td>
                                    <span class="difficulty-badge <?php echo strtolower($quiz['difficulty']); ?>">
                                        <?php echo $quiz['difficulty']; ?>
                                    </span>
                                </td>
                                <td><i class="fas fa-question"></i> <?php echo $quiz['question_count']; ?></td>
                                <td><i class="fas fa-users"></i> <?php echo $quiz['attempts']; ?></td>
                                <td><?php echo clean_input($quiz['full_name']); ?></td>
                                <td><?php echo format_date($quiz['created_at']); ?></td>
                                <td class="action-buttons">
                                    <a href="../quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-icon" title="ნახვა" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="quizzes.php?manage=<?php echo $quiz['id']; ?>" class="btn-icon" title="კითხვები">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="quizzes.php?edit=<?php echo $quiz['id']; ?>" class="btn-icon" title="რედაქტირება">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="quizzes.php?delete=<?php echo $quiz['id']; ?>" 
                                       class="btn-icon btn-delete" 
                                       title="წაშლა"
                                       onclick="return confirm('დარწმუნებული ხართ რომ გსურთ წაშლა?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('quizForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<style>
.difficulty-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.difficulty-badge.მარტივი {
    background: #4ade80;
    color: white;
}

.difficulty-badge.საშუალო {
    background: #fbbf24;
    color: white;
}

.difficulty-badge.რთული {
    background: #ef4444;
    color: white;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #999;
    background: #f8f9fa;
    border-radius: 10px;
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.question-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.question-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.question-num {
    background: #667eea;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.question-points {
    color: #667eea;
    font-weight: 600;
}

.question-item h4 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.question-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
    margin-bottom: 1rem;
}

.option {
    padding: 0.8rem;
    background: white;
    border-radius: 10px;
    border: 2px solid #e0e0e0;
}

.option.correct {
    border-color: #4ade80;
    background: #4ade8015;
    font-weight: 600;
}

.delete-question-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #ef4444;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.delete-question-btn:hover {
    color: #dc2626;
}

@media (max-width: 768px) {
    .question-options {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>