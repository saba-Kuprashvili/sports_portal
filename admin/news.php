<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// წაშლა
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    if ($stmt->execute([$id])) {
        show_message('ახალი ამბები წარმატებით წაიშალა', 'success');
    }
    header('Location: news.php');
    exit;
}

// დამატება/რედაქტირება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $content = $_POST['content'];
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    
    // ფაილის ატვირთვა
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = upload_file($_FILES['image'], 'news');
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        }
    }
    
    if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
        // რედაქტირება
        $id = $_POST['edit_id'];
        
        if ($image) {
            $stmt = $pdo->prepare("
                UPDATE news 
                SET title = ?, content = ?, is_breaking = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $is_breaking, $image, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE news 
                SET title = ?, content = ?, is_breaking = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $is_breaking, $id]);
        }
        
        show_message('ახალი ამბები წარმატებით განახლდა', 'success');
    } else {
        // დამატება
        $stmt = $pdo->prepare("
            INSERT INTO news (user_id, title, content, is_breaking, image) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $is_breaking, $image]);
        
        show_message('ახალი ამბები წარმატებით დაემატა', 'success');
    }
    
    header('Location: news.php');
    exit;
}

// რედაქტირებისთვის მონაცემების წამოღება
$edit_news = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_news = $stmt->fetch();
}

// ყველა ახალი ამბის წამოღება
$stmt = $pdo->query("
    SELECT n.*, u.username, u.full_name 
    FROM news n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.is_breaking DESC, n.created_at DESC
");
$news = $stmt->fetchAll();

$page_title = 'ახალი ამბების მართვა';
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
                <a href="news.php" class="active"><i class="fas fa-rss"></i> ახალი ამბები</a>
                <a href="quizzes.php"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-rss"></i> ახალი ამბების მართვა</h1>
                <button onclick="toggleForm()" class="admin-btn">
                    <i class="fas fa-plus"></i> ახალი ამბები
                </button>
            </div>
            
            <!-- ფორმა -->
            <div id="newsForm" class="admin-form-container" style="display: <?php echo $edit_news ? 'block' : 'none'; ?>;">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <?php if ($edit_news): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_news['id']; ?>">
                    <h2>ახალი ამბის რედაქტირება</h2>
                    <?php else: ?>
                    <h2>ახალი ამბის დამატება</h2>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> სათაური *</label>
                        <input type="text" name="title" required 
                               value="<?php echo $edit_news ? clean_input($edit_news['title']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> შინაარსი *</label>
                        <textarea name="content" rows="8" required><?php echo $edit_news ? $edit_news['content'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> სურათი</label>
                            <input type="file" name="image" accept="image/*" data-preview="imagePreview">
                            <?php if ($edit_news && $edit_news['image']): ?>
                            <img id="imagePreview" src="../uploads/news/<?php echo $edit_news['image']; ?>" 
                                 style="max-width: 200px; margin-top: 10px; border-radius: 10px;">
                            <?php else: ?>
                            <img id="imagePreview" style="display: none; max-width: 200px; margin-top: 10px; border-radius: 10px;">
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label-admin">
                                <input type="checkbox" name="is_breaking" 
                                    <?php echo ($edit_news && $edit_news['is_breaking']) ? 'checked' : ''; ?>>
                                <span><i class="fas fa-bolt"></i> სასწრაფო ამბები</span>
                            </label>
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
            
            <!-- ახალი ამბების სია -->
            <div class="admin-section">
                <h2>ყველა ახალი ამბები (<?php echo count($news); ?>)</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>სურათი</th>
                                <th>სათაური</th>
                                <th>ავტორი</th>
                                <th>ნახვები</th>
                                <th>სასწრაფო</th>
                                <th>თარიღი</th>
                                <th>მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news as $news_item): ?>
                            <tr>
                                <td>#<?php echo $news_item['id']; ?></td>
                                <td>
                                    <?php if ($news_item['image']): ?>
                                    <img src="../uploads/news/<?php echo $news_item['image']; ?>" 
                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                    <div style="width: 60px; height: 40px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo clean_input(truncate_text($news_item['title'], 40)); ?></td>
                                <td><?php echo clean_input($news_item['full_name']); ?></td>
                                <td><i class="fas fa-eye"></i> <?php echo $news_item['views']; ?></td>
                                <td>
                                    <?php if ($news_item['is_breaking']): ?>
                                    <span class="breaking-badge-small"><i class="fas fa-bolt"></i> კი</span>
                                    <?php else: ?>
                                    <span style="color: #999;">არა</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($news_item['created_at']); ?></td>
                                <td class="action-buttons">
                                    <a href="../news_detail.php?id=<?php echo $news_item['id']; ?>" class="btn-icon" title="ნახვა" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="news.php?edit=<?php echo $news_item['id']; ?>" class="btn-icon" title="რედაქტირება">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="news.php?delete=<?php echo $news_item['id']; ?>" 
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
    const form = document.getElementById('newsForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<style>
.checkbox-label-admin {
    display: flex !important;
    align-items: center;
    gap: 10px;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    cursor: pointer;
    margin-top: 10px;
}

.checkbox-label-admin input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.breaking-badge-small {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
</style>

<?php include '../includes/footer.php'; ?>