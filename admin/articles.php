<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// წაშლა
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    if ($stmt->execute([$id])) {
        show_message('სტატია წარმატებით წაიშალა', 'success');
    }
    header('Location: articles.php');
    exit;
}

// დამატება/რედაქტირება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $content = $_POST['content']; // არ ვასუფთავებთ HTML-ს
    $category = clean_input($_POST['category']);
    $status = clean_input($_POST['status']);
    
    // ფაილის ატვირთვა
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = upload_file($_FILES['image'], 'articles');
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        }
    }
    
    if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
        // რედაქტირება
        $id = $_POST['edit_id'];
        
        if ($image) {
            $stmt = $pdo->prepare("
                UPDATE articles 
                SET title = ?, content = ?, category = ?, status = ?, image = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $category, $status, $image, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE articles 
                SET title = ?, content = ?, category = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $category, $status, $id]);
        }
        
        show_message('სტატია წარმატებით განახლდა', 'success');
    } else {
        // დამატება
        $stmt = $pdo->prepare("
            INSERT INTO articles (user_id, title, content, category, status, image) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $category, $status, $image]);
        
        show_message('სტატია წარმატებით დაემატა', 'success');
    }
    
    header('Location: articles.php');
    exit;
}

// რედაქტირებისთვის მონაცემების წამოღება
$edit_article = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_article = $stmt->fetch();
}

// ყველა სტატიის წამოღება
$stmt = $pdo->query("
    SELECT a.*, u.username, u.full_name 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
");
$articles = $stmt->fetchAll();

$page_title = 'სტატიების მართვა';
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
                <a href="articles.php" class="active"><i class="fas fa-newspaper"></i> სტატიები</a>
                <a href="news.php"><i class="fas fa-rss"></i> ახალი ამბები</a>
                <a href="quizzes.php"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-newspaper"></i> სტატიების მართვა</h1>
                <button onclick="toggleForm()" class="admin-btn">
                    <i class="fas fa-plus"></i> ახალი სტატია
                </button>
            </div>
            
            <!-- ფორმა -->
            <div id="articleForm" class="admin-form-container" style="display: <?php echo $edit_article ? 'block' : 'none'; ?>;">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <?php if ($edit_article): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_article['id']; ?>">
                    <h2>სტატიის რედაქტირება</h2>
                    <?php else: ?>
                    <h2>ახალი სტატია</h2>
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> სათაური *</label>
                            <input type="text" name="title" required 
                                   value="<?php echo $edit_article ? clean_input($edit_article['title']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> კატეგორია *</label>
                            <select name="category" required>
                                <option value="">აირჩიეთ კატეგორია</option>
                                <?php 
                                $categories = ['ფეხბურთი', 'კალათბურთი', 'ტენისი', 'ცურვა', 'ძალოვანი სპორტი', 'სხვა'];
                                foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" 
                                    <?php echo ($edit_article && $edit_article['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> შინაარსი *</label>
                        <textarea name="content" rows="10" required><?php echo $edit_article ? $edit_article['content'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> სურათი</label>
                            <input type="file" name="image" accept="image/*" data-preview="imagePreview">
                            <?php if ($edit_article && $edit_article['image']): ?>
                            <img id="imagePreview" src="../uploads/articles/<?php echo $edit_article['image']; ?>" 
                                 style="max-width: 200px; margin-top: 10px; border-radius: 10px;">
                            <?php else: ?>
                            <img id="imagePreview" style="display: none; max-width: 200px; margin-top: 10px; border-radius: 10px;">
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-eye"></i> სტატუსი *</label>
                            <select name="status" required>
                                <option value="published" <?php echo ($edit_article && $edit_article['status'] === 'published') ? 'selected' : ''; ?>>
                                    გამოქვეყნებული
                                </option>
                                <option value="draft" <?php echo ($edit_article && $edit_article['status'] === 'draft') ? 'selected' : ''; ?>>
                                    დრაფტი
                                </option>
                            </select>
                        </div>
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
            
            <!-- სტატიების სია -->
            <div class="admin-section">
                <h2>ყველა სტატია (<?php echo count($articles); ?>)</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>სურათი</th>
                                <th>სათაური</th>
                                <th>კატეგორია</th>
                                <th>ავტორი</th>
                                <th>ნახვები</th>
                                <th>სტატუსი</th>
                                <th>თარიღი</th>
                                <th>მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>#<?php echo $article['id']; ?></td>
                                <td>
                                    <?php if ($article['image']): ?>
                                    <img src="../uploads/articles/<?php echo $article['image']; ?>" 
                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                    <div style="width: 60px; height: 40px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo clean_input(truncate_text($article['title'], 40)); ?></td>
                                <td><span class="badge"><?php echo $article['category']; ?></span></td>
                                <td><?php echo clean_input($article['full_name']); ?></td>
                                <td><i class="fas fa-eye"></i> <?php echo $article['views']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $article['status']; ?>">
                                        <?php echo $article['status'] === 'published' ? 'გამოქვეყნებული' : 'დრაფტი'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($article['created_at']); ?></td>
                                <td class="action-buttons">
                                    <a href="../article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="ნახვა" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="articles.php?edit=<?php echo $article['id']; ?>" class="btn-icon" title="რედაქტირება">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="articles.php?delete=<?php echo $article['id']; ?>" 
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
        </main>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('articleForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.admin-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.admin-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.admin-btn-secondary {
    background: #6c757d;
}

.admin-btn-secondary:hover {
    background: #5a6268;
}

.admin-form-container {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.admin-form h2 {
    color: #667eea;
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-delete {
    color: #ef4444 !important;
}

.btn-delete:hover {
    color: #dc2626 !important;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .admin-header {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>