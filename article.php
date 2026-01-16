<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? 0;

// სტატიის წამოღება
$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.full_name, u.profile_image 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit;
}

// ნახვების განახლება
$stmt = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$stmt->execute([$id]);

// რეიტინგის წამოღება
$rating_data = get_average_rating('article', $id, $pdo);

// კომენტარების წამოღება
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.full_name, u.profile_image 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.article_id = ?
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
            INSERT INTO comments (user_id, article_id, content) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $id, $comment])) {
            show_message('კომენტარი წარმატებით დაემატა', 'success');
            header("Location: article.php?id=$id");
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
            INSERT INTO ratings (user_id, article_id, rating) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $id, $rating, $rating]);
        
        show_message('შეფასება წარმატებით დაემატა', 'success');
        header("Location: article.php?id=$id");
        exit;
    }
}

// მსგავსი სტატიები
$stmt = $pdo->prepare("
    SELECT * FROM articles 
    WHERE category = ? AND id != ? AND status = 'published'
    ORDER BY RAND()
    LIMIT 3
");
$stmt->execute([$article['category'], $id]);
$related = $stmt->fetchAll();

$page_title = $article['title'];
include 'includes/header.php';
?>

<input type="hidden" id="articleId" value="<?php echo $id; ?>">

<div class="container">
    <article class="article-detail">
        <div class="article-detail-header">
            <span class="article-category-large"><?php echo clean_input($article['category']); ?></span>
            <h1><?php echo clean_input($article['title']); ?></h1>
            
            <div class="article-detail-meta">
                <div class="author-info">
                    <img src="uploads/profiles/<?php echo $article['profile_image']; ?>" 
                         alt="<?php echo clean_input($article['full_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/50'">
                    <div>
                        <strong><?php echo clean_input($article['full_name']); ?></strong>
                        <span><?php echo format_date($article['created_at']); ?></span>
                    </div>
                </div>
                
                <div class="article-stats">
                    <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                    <span><i class="fas fa-comments"></i> <?php echo count($comments); ?></span>
                    <span><i class="fas fa-star"></i> <?php echo number_format($rating_data['avg_rating'] ?? 0, 1); ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($article['image']): ?>
        <div class="article-detail-image">
            <img src="uploads/articles/<?php echo clean_input($article['image']); ?>" 
                 alt="<?php echo clean_input($article['title']); ?>">
        </div>
        <?php endif; ?>
        
        <div class="article-detail-content">
            <?php echo nl2br($article['content']); ?>
        </div>
        
        <!-- რეიტინგი -->
        <div class="article-rating-section">
            <h3>შეაფასეთ ეს სტატია</h3>
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
    
    <!-- მსგავსი სტატიები -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2><i class="fas fa-newspaper"></i> მსგავსი სტატიები</h2>
        <div class="related-grid">
            <?php foreach ($related as $rel): ?>
            <a href="article.php?id=<?php echo $rel['id']; ?>" class="related-card">
                <?php if ($rel['image']): ?>
                <img src="uploads/articles/<?php echo $rel['image']; ?>" alt="<?php echo clean_input($rel['title']); ?>">
                <?php endif; ?>
                <h3><?php echo clean_input($rel['title']); ?></h3>
                <span class="related-meta"><i class="fas fa-eye"></i> <?php echo $rel['views']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
.article-detail {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    margin: 2rem 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.article-detail-header {
    margin-bottom: 2rem;
}

.article-category-large {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    margin-bottom: 1rem;
}

.article-detail h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.article-detail-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-info img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.author-info strong {
    display: block;
    color: #333;
}

.author-info span {
    font-size: 0.9rem;
    color: #999;
}

.article-stats {
    display: flex;
    gap: 2rem;
    font-size: 0.95rem;
    color: #666;
}

.article-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.article-detail-image {
    margin: 2rem 0;
    border-radius: 15px;
    overflow: hidden;
}

.article-detail-image img {
    width: 100%;
    height: auto;
}

.article-detail-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
    margin: 2rem 0;
}

.article-rating-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    margin: 2rem 0;
}

.article-rating-section h3 {
    color: #333;
    margin-bottom: 1rem;
}

.rating-form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.rating-stars {
    display: flex;
    gap: 0.5rem;
}

.rating-stars label {
    cursor: pointer;
}

.rating-stars input {
    display: none;
}

.rating-stars input:checked ~ i,
.rating-stars label:hover i,
.rating-stars label:hover ~ label i {
    color: #fbbf24;
}

.rate-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.rate-btn:hover {
    transform: scale(1.05);
}

.rating-display {
    margin-top: 1rem;
    color: #666;
}

.comments-section {
    margin-top: 3rem;
}

.comments-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
}

.comment-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    resize: vertical;
    font-family: inherit;
    margin-bottom: 1rem;
}

.comment-form textarea:focus {
    outline: none;
    border-color: #667eea;
}

.comment-btn {
    background: #667eea;
    color: white;
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.comment-btn:hover {
    background: #5568d3;
}

.login-prompt {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 2rem;
}

.login-prompt a {
    color: #667eea;
    font-weight: 600;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.no-comments {
    text-align: center;
    color: #999;
    padding: 2rem;
}

.comment-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
}

.comment-item img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.comment-header strong {
    color: #333;
}

.comment-header span {
    color: #999;
    font-size: 0.9rem;
}

.comment-content p {
    color: #666;
    line-height: 1.6;
}

.related-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.related-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.related-card {
    display: block;
    background: #f8f9fa;
    border-radius: 15px;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.3s;
}

.related-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.related-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.related-card h3 {
    padding: 1rem;
    color: #333;
    font-size: 1rem;
}

.related-meta {
    padding: 0 1rem 1rem;
    color: #999;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .article-detail {
        padding: 1.5rem;
    }
    
    .article-detail h1 {
        font-size: 1.8rem;
    }
    
    .article-detail-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>