<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? 0;

// ახალი ამბის წამოღება
$stmt = $pdo->prepare("
    SELECT n.*, u.username, u.full_name, u.profile_image 
    FROM news n
    JOIN users u ON n.user_id = u.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header('Location: index.php');
    exit;
}

// ნახვების განახლება
$stmt = $pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?");
$stmt->execute([$id]);

// რეიტინგის წამოღება
$rating_data = get_average_rating('news', $id, $pdo);

// კომენტარების წამოღება
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.full_name, u.profile_image 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.news_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// კომენტარის დამატება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    require_login();
    
    $comment = clean_input($_POST['comment']);
    
    if (!empty($comment)) {
        $stmt = $pdo->prepare("
            INSERT INTO comments (user_id, news_id, content) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $id, $comment])) {
            show_message('კომენტარი წარმატებით დაემატა', 'success');
            header("Location: news_detail.php?id=$id");
            exit;
        }
    }
}

// რეიტინგის დამატება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate'])) {
    require_login();
    
    $rating = intval($_POST['rating']);
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("
            INSERT INTO ratings (user_id, news_id, rating) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $id, $rating, $rating]);
        
        show_message('შეფასება წარმატებით დაემატა', 'success');
        header("Location: news_detail.php?id=$id");
        exit;
    }
}

// მსგავსი ახალი ამბები
$stmt = $pdo->prepare("
    SELECT * FROM news 
    WHERE id != ?
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->execute([$id]);
$related = $stmt->fetchAll();

$page_title = $news['title'];
include 'includes/header.php';
?>

<input type="hidden" id="newsId" value="<?php echo $id; ?>">

<div class="container">
    <article class="news-detail">
        <div class="news-detail-header">
            <?php if ($news['is_breaking']): ?>
            <span class="breaking-badge-large">
                <i class="fas fa-bolt"></i> სასწრაფო
            </span>
            <?php endif; ?>
            
            <h1><?php echo clean_input($news['title']); ?></h1>
            
            <div class="news-detail-meta">
                <div class="author-info">
                    <img src="uploads/profiles/<?php echo $news['profile_image']; ?>" 
                         alt="<?php echo clean_input($news['full_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/50'">
                    <div>
                        <strong><?php echo clean_input($news['full_name']); ?></strong>
                        <span><?php echo format_date($news['created_at']); ?></span>
                    </div>
                </div>
                
                <div class="news-stats">
                    <span><i class="fas fa-eye"></i> <?php echo $news['views']; ?></span>
                    <span><i class="fas fa-comments"></i> <?php echo count($comments); ?></span>
                    <span><i class="fas fa-star"></i> <?php echo number_format($rating_data['avg_rating'] ?? 0, 1); ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($news['image']): ?>
        <div class="news-detail-image">
            <img src="uploads/news/<?php echo clean_input($news['image']); ?>" 
                 alt="<?php echo clean_input($news['title']); ?>">
        </div>
        <?php endif; ?>
        
        <div class="news-detail-content">
            <?php echo nl2br($news['content']); ?>
        </div>
        
        <!-- რეიტინგი -->
        <div class="rating-section">
            <h3>შეაფასეთ ეს ახალი ამბები</h3>
            <?php if (is_logged_in()): ?>
            <form method="POST" class="rating-form">
                <input type="hidden" name="rate" value="1">
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label>
                        <input type="radio" name="rating" value="<?php echo $i; ?>" required>
                        <i class="fas fa-star rating-star"></i>
                    </label>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="rate-btn">შეფასება</button>
            </form>
            <div class="rating-display">
                <span>საშუალო: <strong id="avgRating"><?php echo number_format($rating_data['avg_rating'] ?? 0, 1); ?></strong></span>
                <span>(<span id="ratingCount"><?php echo $rating_data['total_ratings'] ?? 0; ?></span> შეფასება)</span>
            </div>
            <?php else: ?>
            <p><a href="login.php">შესვლა</a> შეფასების დასამატებლად</p>
            <?php endif; ?>
        </div>
        
        <!-- კომენტარები -->
        <div class="comments-section">
            <h2><i class="fas fa-comments"></i> კომენტარები (<?php echo count($comments); ?>)</h2>
            
            <?php if (is_logged_in()): ?>
            <form method="POST" class="comment-form">
                <input type="hidden" name="add_comment" value="1">
                <textarea name="comment" placeholder="დაწერე შენი აზრი..." rows="4" required></textarea>
                <button type="submit" class="comment-btn">
                    <i class="fas fa-paper-plane"></i> გაგზავნა
                </button>
            </form>
            <?php else: ?>
            <div class="login-prompt">
                <p><a href="login.php">შესვლა</a> კომენტარის დასამატებლად</p>
            </div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                <p class="no-comments">ჯერ არ არის კომენტარები. იყავი პირველი!</p>
                <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <img src="uploads/profiles/<?php echo $comment['profile_image']; ?>" 
                         alt="<?php echo clean_input($comment['full_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/50'">
                    <div class="comment-content">
                        <div class="comment-header">
                            <strong><?php echo clean_input($comment['full_name']); ?></strong>
                            <span><?php echo format_date($comment['created_at']); ?></span>
                        </div>
                        <p><?php echo nl2br(clean_input($comment['content'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </article>
    
    <!-- მსგავსი ახალი ამბები -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2><i class="fas fa-rss"></i> სხვა ახალი ამბები</h2>
        <div class="related-grid">
            <?php foreach ($related as $rel): ?>
            <a href="news_detail.php?id=<?php echo $rel['id']; ?>" class="related-card">
                <?php if ($rel['image']): ?>
                <img src="uploads/news/<?php echo $rel['image']; ?>" alt="<?php echo clean_input($rel['title']); ?>">
                <?php endif; ?>
                <h3><?php echo clean_input($rel['title']); ?></h3>
                <span class="related-meta">
                    <i class="fas fa-calendar"></i> <?php echo format_date($rel['created_at']); ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.news-detail {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    margin: 2rem 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.news-detail-header {
    margin-bottom: 2rem;
}

.breaking-badge-large {
    display: inline-block;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 0.6rem 1.5rem;
    border-radius: 25px;
    font-weight: 700;
    margin-bottom: 1rem;
    animation: pulse 2s infinite;
}

.news-detail h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.news-detail-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.news-detail-image {
    margin: 2rem 0;
    border-radius: 15px;
    overflow: hidden;
}

.news-detail-image img {
    width: 100%;
    height: auto;
}

.news-detail-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
    margin: 2rem 0;
}

.rating-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .news-detail {
        padding: 1.5rem;
    }
    
    .news-detail h1 {
        font-size: 1.8rem;
    }
    
    .news-detail-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>