<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// ფილტრი კატეგორიით
$category = $_GET['category'] ?? '';
$author = $_GET['author'] ?? '';

// SQL Query
$query = "
    SELECT a.*, u.username, u.full_name 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'published'
";

if ($category) {
    $query .= " AND a.category = :category";
}

if ($author) {
    $query .= " AND a.user_id = :author";
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($query);

if ($category) {
    $stmt->bindParam(':category', $category);
}

if ($author) {
    $stmt->bindParam(':author', $author);
}

$stmt->execute();
$articles = $stmt->fetchAll();

// კატეგორიები
$categories = ['ფეხბურთი', 'კალათბურთი', 'ტენისი', 'ცურვა', 'ძალოვანი სპორტი', 'სხვა'];

// პოპულარული სტატიები
$popular = get_popular_articles($pdo, 5);

$page_title = 'ყველა სტატია';
include 'includes/header.php';
?>

<div class="container">
    <div class="articles-page">
        <aside class="articles-sidebar">
            <div class="sidebar-widget">
                <h3><i class="fas fa-filter"></i> კატეგორიები</h3>
                <div class="category-list">
                    <a href="articles.php" class="category-item <?php echo !$category ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> ყველა
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="articles.php?category=<?php echo urlencode($cat); ?>" 
                       class="category-item <?php echo $category === $cat ? 'active' : ''; ?>">
                        <i class="fas fa-tag"></i> <?php echo $cat; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="sidebar-widget">
                <h3><i class="fas fa-fire"></i> პოპულარული</h3>
                <div class="popular-articles">
                    <?php foreach ($popular as $pop): ?>
                    <a href="article.php?id=<?php echo $pop['id']; ?>" class="popular-article-item">
                        <?php if ($pop['image']): ?>
                        <img src="uploads/articles/<?php echo $pop['image']; ?>" alt="<?php echo clean_input($pop['title']); ?>">
                        <?php endif; ?>
                        <div>
                            <h4><?php echo clean_input(truncate_text($pop['title'], 40)); ?></h4>
                            <span><i class="fas fa-eye"></i> <?php echo $pop['views']; ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
        
        <main class="articles-main">
            <div class="articles-header">
                <h1><i class="fas fa-newspaper"></i> 
                    <?php if ($category): ?>
                        <?php echo $category; ?>
                    <?php elseif ($author): ?>
                        ჩემი სტატიები
                    <?php else: ?>
                        ყველა სტატია
                    <?php endif; ?>
                </h1>
                <p class="articles-count">ნაპოვნია <?php echo count($articles); ?> სტატია</p>
            </div>
            
            <?php if (empty($articles)): ?>
            <div class="no-articles">
                <i class="fas fa-newspaper" style="font-size: 5rem; color: #ddd;"></i>
                <h2>სტატიები არ მოიძებნა</h2>
            </div>
            <?php else: ?>
            <div class="articles-grid-full">
                <?php foreach ($articles as $article): ?>
                <article class="article-card-full">
                    <?php if ($article['image']): ?>
                    <div class="article-image">
                        <img src="uploads/articles/<?php echo clean_input($article['image']); ?>" 
                             alt="<?php echo clean_input($article['title']); ?>">
                        <span class="article-category"><?php echo clean_input($article['category']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <h3>
                            <a href="article.php?id=<?php echo $article['id']; ?>">
                                <?php echo clean_input($article['title']); ?>
                            </a>
                        </h3>
                        
                        <p class="article-excerpt">
                            <?php echo truncate_text(clean_input($article['content']), 150); ?>
                        </p>
                        
                        <div class="article-meta">
                            <div class="author-info-small">
                                <img src="uploads/profiles/<?php echo $article['profile_image'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo clean_input($article['full_name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/30'">
                                <span><?php echo clean_input($article['full_name']); ?></span>
                            </div>
                            <div class="article-stats-small">
                                <span><i class="fas fa-calendar"></i> <?php echo format_date($article['created_at']); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                            </div>
                        </div>
                        
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more-btn">
                            <i class="fas fa-arrow-right"></i> სრულად წაკითხვა
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.articles-page {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.articles-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.sidebar-widget {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.sidebar-widget h3 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-item {
    padding: 1rem;
    border-radius: 10px;
    text-decoration: none;
    color: #666;
    background: #f8f9fa;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-item:hover,
.category-item.active {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    color: #667eea;
    transform: translateX(5px);
}

.popular-articles {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.popular-article-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
}

.popular-article-item:hover {
    background: #667eea15;
    transform: translateX(3px);
}

.popular-article-item img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.popular-article-item h4 {
    color: #333;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.popular-article-item span {
    color: #999;
    font-size: 0.8rem;
}

.articles-main {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.articles-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #667eea;
}

.articles-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.articles-count {
    color: #999;
    font-size: 0.9rem;
}

.no-articles {
    text-align: center;
    padding: 5rem 2rem;
}

.no-articles h2 {
    color: #999;
    margin-top: 1rem;
}

.articles-grid-full {
    display: grid;
    gap: 2rem;
}

.article-card-full {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    transition: all 0.3s;
}

.article-card-full:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}

.article-card-full .article-image {
    position: relative;
    height: 200px;
    border-radius: 15px;
    overflow: hidden;
}

.article-card-full .article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.article-card-full:hover .article-image img {
    transform: scale(1.1);
}

.author-info-small {
    display: flex;
    align-items: center;
    gap: 8px;
}

.author-info-small img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.author-info-small span {
    font-weight: 600;
    color: #333;
}

.article-stats-small {
    display: flex;
    gap: 1rem;
    color: #999;
    font-size: 0.85rem;
}

.read-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 1rem;
    padding: 0.8rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
}

.read-more-btn:hover {
    transform: translateX(5px);
}

@media (max-width: 968px) {
    .articles-page {
        grid-template-columns: 1fr;
    }
    
    .articles-sidebar {
        position: static;
    }
    
    .article-card-full {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>