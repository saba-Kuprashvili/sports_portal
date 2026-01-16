<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// წაშლა
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    if ($stmt->execute([$id])) {
        show_message('კომენტარი წარმატებით წაიშალა', 'success');
    }
    header('Location: comments.php');
    exit;
}

// ფილტრი
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// კომენტარების წამოღება
$query = "
    SELECT c.*, 
           u.username, u.full_name,
           a.title as article_title,
           n.title as news_title
    FROM comments c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN articles a ON c.article_id = a.id
    LEFT JOIN news n ON c.news_id = n.id
    WHERE 1=1
";

if ($search) {
    $query .= " AND (c.content LIKE :search OR u.username LIKE :search)";
}

if ($filter === 'articles') {
    $query .= " AND c.article_id IS NOT NULL";
} elseif ($filter === 'news') {
    $query .= " AND c.news_id IS NOT NULL";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);

if ($search) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$comments = $stmt->fetchAll();

// სტატისტიკა
$stats = [
    'total' => count($comments),
    'articles' => count(array_filter($comments, fn($c) => $c['article_id'])),
    'news' => count(array_filter($comments, fn($c) => $c['news_id']))
];

$page_title = 'კომენტარების მოდერაცია';
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
                <a href="quizzes.php"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php" class="active"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h1><i class="fas fa-comments"></i> კომენტარების მოდერაცია</h1>
            
            <!-- სტატისტიკა -->
            <div class="comment-stats">
                <div class="stat-box">
                    <i class="fas fa-comments"></i>
                    <div>
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>სულ კომენტარი</p>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-newspaper"></i>
                    <div>
                        <h3><?php echo $stats['articles']; ?></h3>
                        <p>სტატიებზე</p>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-rss"></i>
                    <div>
                        <h3><?php echo $stats['news']; ?></h3>
                        <p>ახალ ამბებზე</p>
                    </div>
                </div>
            </div>
            
            <!-- ფილტრი და ძიება -->
            <div class="filter-section">
                <div class="filter-buttons">
                    <a href="comments.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> ყველა
                    </a>
                    <a href="comments.php?filter=articles" class="filter-btn <?php echo $filter === 'articles' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i> სტატიები
                    </a>
                    <a href="comments.php?filter=news" class="filter-btn <?php echo $filter === 'news' ? 'active' : ''; ?>">
                        <i class="fas fa-rss"></i> ახალი ამბები
                    </a>
                </div>
                
                <form method="GET" class="search-form">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <input type="text" name="search" placeholder="მოძებნე კომენტარი..." 
                           value="<?php echo clean_input($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <!-- კომენტარების სია -->
            <div class="admin-section">
                <?php if (empty($comments)): ?>
                <p class="no-data">კომენტარები არ მოიძებნა</p>
                <?php else: ?>
                <div class="comments-grid">
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <div class="comment-user">
                                <img src="../uploads/profiles/<?php echo $comment['profile_image'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo clean_input($comment['username']); ?>"
                                     onerror="this.src='https://via.placeholder.com/40'">
                                <div>
                                    <strong><?php echo clean_input($comment['full_name']); ?></strong>
                                    <span class="comment-username">@<?php echo clean_input($comment['username']); ?></span>
                                </div>
                            </div>
                            <span class="comment-date"><?php echo format_date($comment['created_at']); ?></span>
                        </div>
                        
                        <div class="comment-content">
                            <p><?php echo nl2br(clean_input($comment['content'])); ?></p>
                        </div>
                        
                        <div class="comment-meta">
                            <div class="comment-source">
                                <?php if ($comment['article_id']): ?>
                                <i class="fas fa-newspaper"></i>
                                <a href="../article.php?id=<?php echo $comment['article_id']; ?>" target="_blank">
                                    <?php echo truncate_text(clean_input($comment['article_title']), 40); ?>
                                </a>
                                <?php elseif ($comment['news_id']): ?>
                                <i class="fas fa-rss"></i>
                                <a href="../news_detail.php?id=<?php echo $comment['news_id']; ?>" target="_blank">
                                    <?php echo truncate_text(clean_input($comment['news_title']), 40); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <a href="comments.php?delete=<?php echo $comment['id']; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="delete-comment-btn"
                               onclick="return confirm('დარწმუნებული ხართ რომ გსურთ კომენტარის წაშლა?');">
                                <i class="fas fa-trash"></i> წაშლა
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
.comment-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    padding: 1.5rem;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-box i {
    font-size: 2.5rem;
    color: #667eea;
}

.stat-box h3 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 0.3rem;
}

.stat-box p {
    color: #666;
    font-size: 0.9rem;
}

.filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.filter-buttons {
    display: flex;
    gap: 1rem;
}

.filter-btn {
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    color: #666;
    background: #f8f9fa;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.filter-btn:hover,
.filter-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.search-form {
    display: flex;
    gap: 0.5rem;
}

.search-form input {
    padding: 0.8rem 1.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    min-width: 250px;
    outline: none;
}

.search-form input:focus {
    border-color: #667eea;
}

.search-form button {
    padding: 0.8rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s;
}

.search-form button:hover {
    background: #5568d3;
}

.comments-grid {
    display: grid;
    gap: 1.5rem;
}

.comment-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.comment-user {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.comment-user img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-user strong {
    display: block;
    color: #333;
}

.comment-username {
    color: #999;
    font-size: 0.85rem;
}

.comment-date {
    color: #999;
    font-size: 0.85rem;
}

.comment-content {
    margin-bottom: 1rem;
}

.comment-content p {
    color: #333;
    line-height: 1.6;
}

.comment-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
}

.comment-source {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.comment-source i {
    color: #667eea;
}

.comment-source a {
    color: #667eea;
    text-decoration: none;
}

.comment-source a:hover {
    text-decoration: underline;
}

.delete-comment-btn {
    color: #ef4444;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.delete-comment-btn:hover {
    color: #dc2626;
}

@media (max-width: 768px) {
    .filter-section {
        flex-direction: column;
    }
    
    .filter-buttons {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .search-form {
        width: 100%;
    }
    
    .search-form input {
        flex: 1;
        min-width: auto;
    }
}
</style>

<?php include '../includes/footer.php'; ?>