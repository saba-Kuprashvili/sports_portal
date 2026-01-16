<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$user_id = $_SESSION['user_id'];

// პროფილის განახლება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    
    // ფაილის ატვირთვა
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_result = upload_file($_FILES['profile_image'], 'profiles');
        if ($upload_result['success']) {
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$upload_result['filename'], $user_id]);
        }
    }
    
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $user_id])) {
        $_SESSION['full_name'] = $full_name;
        show_message('პროფილი წარმატებით განახლდა', 'success');
    }
    
    header('Location: profile.php');
    exit;
}

// პაროლის შეცვლა
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // მიმდინარე პაროლის შემოწმება
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        show_message('არასწორი მიმდინარე პაროლი', 'error');
    } elseif ($new_password !== $confirm_password) {
        show_message('პაროლები არ ემთხვევა', 'error');
    } elseif (strlen($new_password) < 6) {
        show_message('პაროლი უნდა შეიცავდეს მინიმუმ 6 სიმბოლოს', 'error');
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            show_message('პაროლი წარმატებით შეიცვალა', 'success');
        }
    }
    
    header('Location: profile.php');
    exit;
}

// მომხმარებლის ინფორმაცია
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// სტატისტიკა
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM articles WHERE user_id = ?) as article_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = ?) as comment_count,
        (SELECT COUNT(*) FROM quiz_results WHERE user_id = ?) as quiz_count
");
$stmt->execute([$user_id, $user_id, $user_id]);
$stats = $stmt->fetch();

// მომხმარებლის სტატიები
$stmt = $pdo->prepare("
    SELECT * FROM articles 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$user_articles = $stmt->fetchAll();

// მომხმარებლის კომენტარები
$stmt = $pdo->prepare("
    SELECT c.*, 
           a.title as article_title,
           n.title as news_title
    FROM comments c
    LEFT JOIN articles a ON c.article_id = a.id
    LEFT JOIN news n ON c.news_id = n.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$user_comments = $stmt->fetchAll();

// ქვიზების შედეგები
$stmt = $pdo->prepare("
    SELECT qr.*, q.title as quiz_title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.user_id = ?
    ORDER BY qr.completed_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$quiz_results = $stmt->fetchAll();

$page_title = 'პროფილი';
include 'includes/header.php';
?>

<div class="container">
    <div class="profile-page">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <img src="uploads/profiles/<?php echo $user['profile_image']; ?>" 
                         alt="<?php echo clean_input($user['full_name']); ?>"
                         id="profileImagePreview"
                         onerror="this.src='https://via.placeholder.com/150'">
                </div>
                
                <h2><?php echo clean_input($user['full_name']); ?></h2>
                <p class="profile-username">@<?php echo clean_input($user['username']); ?></p>
                <p class="profile-email"><?php echo clean_input($user['email']); ?></p>
                
                <?php if ($user['role'] === 'admin'): ?>
                <span class="profile-badge admin-badge">
                    <i class="fas fa-crown"></i> ადმინისტრატორი
                </span>
                <?php endif; ?>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <i class="fas fa-newspaper"></i>
                        <div>
                            <strong><?php echo $stats['article_count']; ?></strong>
                            <span>სტატია</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-comments"></i>
                        <div>
                            <strong><?php echo $stats['comment_count']; ?></strong>
                            <span>კომენტარი</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-brain"></i>
                        <div>
                            <strong><?php echo $stats['quiz_count']; ?></strong>
                            <span>ქვიზი</span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-dates">
                    <p><i class="fas fa-calendar"></i> რეგისტრაცია: <?php echo format_date($user['created_at']); ?></p>
                    <?php if ($user['last_login']): ?>
                    <p><i class="fas fa-clock"></i> ბოლო შესვლა: <?php echo format_date($user['last_login']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="profile-content">
            <!-- პროფილის რედაქტირება -->
            <div class="profile-section">
                <h2><i class="fas fa-user-edit"></i> პროფილის რედაქტირება</h2>
                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> სახელი და გვარი</label>
                        <input type="text" name="full_name" required 
                               value="<?php echo clean_input($user['full_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> ელ.ფოსტა</label>
                        <input type="email" name="email" required 
                               value="<?php echo clean_input($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> პროფილის სურათი</label>
                        <input type="file" name="profile_image" accept="image/*" 
                               data-preview="profileImagePreview">
                    </div>
                    
                    <button type="submit" class="profile-btn">
                        <i class="fas fa-save"></i> შენახვა
                    </button>
                </form>
            </div>
            
            <!-- პაროლის შეცვლა -->
            <div class="profile-section">
                <h2><i class="fas fa-lock"></i> პაროლის შეცვლა</h2>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> მიმდინარე პაროლი</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> ახალი პაროლი</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> პაროლის დადასტურება</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="profile-btn">
                        <i class="fas fa-check"></i> პაროლის შეცვლა
                    </button>
                </form>
            </div>
            
            <!-- ჩემი სტატიები -->
            <?php if (!empty($user_articles)): ?>
            <div class="profile-section">
                <h2><i class="fas fa-newspaper"></i> ჩემი სტატიები</h2>
                <div class="profile-items-list">
                    <?php foreach ($user_articles as $article): ?>
                    <div class="profile-item">
                        <div>
                            <h4><a href="article.php?id=<?php echo $article['id']; ?>">
                                <?php echo clean_input($article['title']); ?>
                            </a></h4>
                            <p class="item-meta">
                                <span class="badge"><?php echo $article['category']; ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                                <span><?php echo format_date($article['created_at']); ?></span>
                            </p>
                        </div>
                        <span class="status-badge <?php echo $article['status']; ?>">
                            <?php echo $article['status'] === 'published' ? 'გამოქვეყნებული' : 'დრაფტი'; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="articles.php?author=<?php echo $user_id; ?>" class="view-more-link">
                    ყველას ნახვა <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- ჩემი კომენტარები -->
            <?php if (!empty($user_comments)): ?>
            <div class="profile-section">
                <h2><i class="fas fa-comments"></i> ჩემი კომენტარები</h2>
                <div class="profile-items-list">
                    <?php foreach ($user_comments as $comment): ?>
                    <div class="profile-item">
                        <div>
                            <p class="comment-text"><?php echo truncate_text(clean_input($comment['content']), 100); ?></p>
                            <p class="item-meta">
                                <?php if ($comment['article_id']): ?>
                                <a href="article.php?id=<?php echo $comment['article_id']; ?>">
                                    <i class="fas fa-newspaper"></i> <?php echo truncate_text($comment['article_title'], 30); ?>
                                </a>
                                <?php elseif ($comment['news_id']): ?>
                                <a href="news_detail.php?id=<?php echo $comment['news_id']; ?>">
                                    <i class="fas fa-rss"></i> <?php echo truncate_text($comment['news_title'], 30); ?>
                                </a>
                                <?php endif; ?>
                                <span><?php echo format_date($comment['created_at']); ?></span>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ქვიზების შედეგები -->
            <?php if (!empty($quiz_results)): ?>
            <div class="profile-section">
                <h2><i class="fas fa-brain"></i> ქვიზების შედეგები</h2>
                <div class="quiz-results-list">
                    <?php foreach ($quiz_results as $result): ?>
                    <div class="quiz-result-item">
                        <div>
                            <h4><?php echo clean_input($result['quiz_title']); ?></h4>
                            <p><?php echo format_date($result['completed_at']); ?></p>
                        </div>
                        <div class="quiz-score">
                            <span class="score-big"><?php echo $result['score']; ?></span>
                            <span class="score-divider">/</span>
                            <span><?php echo $result['total_questions']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-page {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.profile-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.profile-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.profile-avatar {
    width: 150px;
    height: 150px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid #667eea;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-card h2 {
    color: #333;
    margin-bottom: 0.5rem;
}

.profile-username {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.profile-email {
    color: #999;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.profile-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.admin-badge {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 1.5rem 0;
    padding: 1.5rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.stat-item i {
    font-size: 1.5rem;
    color: #667eea;
}

.stat-item strong {
    display: block;
    font-size: 1.5rem;
    color: #333;
}

.stat-item span {
    font-size: 0.85rem;
    color: #999;
}

.profile-dates {
    text-align: left;
    color: #666;
    font-size: 0.9rem;
}

.profile-dates p {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-dates i {
    color: #667eea;
}

.profile-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.profile-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
    align-self: flex-start;
}

.profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.profile-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.profile-item {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-item h4 {
    margin-bottom: 0.5rem;
}

.profile-item h4 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.profile-item h4 a:hover {
    color: #667eea;
}

.item-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
    color: #999;
}

.item-meta a {
    color: #667eea;
    text-decoration: none;
}

.comment-text {
    color: #666;
    margin-bottom: 0.5rem;
}

.quiz-results-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quiz-result-item {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea15, #764ba215);
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.quiz-result-item h4 {
    color: #333;
    margin-bottom: 0.3rem;
}

.quiz-result-item p {
    color: #999;
    font-size: 0.85rem;
}

.quiz-score {
    display: flex;
    align-items: baseline;
    gap: 5px;
    font-weight: bold;
}

.score-big {
    font-size: 2.5rem;
    color: #667eea;
}

.score-divider {
    font-size: 1.5rem;
    color: #999;
}

.view-more-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.3s;
}

.view-more-link:hover {
    gap: 12px;
}

@media (max-width: 968px) {
    .profile-page {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        position: static;
    }
}
</style>

<?php include 'includes/footer.php'; ?>