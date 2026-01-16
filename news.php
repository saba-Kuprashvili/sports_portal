<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// ყველა ახალი ამბის წამოღება
$stmt = $pdo->query("
    SELECT n.*, u.username, u.full_name 
    FROM news n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.is_breaking DESC, n.created_at DESC
");
$news = $stmt->fetchAll();

// სასწრაფო ახალი ამბები
$breaking = array_filter($news, fn($n) => $n['is_breaking']);
$regular = array_filter($news, fn($n) => !$n['is_breaking']);

$page_title = 'ახალი ამბები';
include 'includes/header.php';
?>

<div class="container">
    <div class="news-page">
        <div class="news-page-header">
            <h1><i class="fas fa-rss"></i> ახალი ამბები</h1>
            <p class="news-count">სულ: <?php echo count($news); ?> ახალი ამბები</p>
        </div>
        
        <!-- სასწრაფო ახალი ამბები -->
        <?php if (!empty($breaking)): ?>
        <section class="breaking-section">
            <h2><i class="fas fa-bolt"></i> სასწრაფო ახალი ამბები</h2>
            <div class="breaking-grid">
                <?php foreach ($breaking as $news_item): ?>
                <article class="news-card breaking-card">
                    <?php if ($news_item['image']): ?>
                    <div class="news-image">
                        <img src="uploads/news/<?php echo clean_input($news_item['image']); ?>" 
                             alt="<?php echo clean_input($news_item['title']); ?>">
                        <span class="breaking-badge">
                            <i class="fas fa-bolt"></i> სასწრაფო
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <h3>
                            <a href="news_detail.php?id=<?php echo $news_item['id']; ?>">
                                <?php echo clean_input($news_item['title']); ?>
                            </a>
                        </h3>
                        
                        <p><?php echo truncate_text(clean_input($news_item['content']), 120); ?></p>
                        
                        <div class="news-meta">
                            <span><i class="fas fa-user"></i> <?php echo clean_input($news_item['full_name']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo format_date($news_item['created_at']); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo $news_item['views']; ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- რეგულარული ახალი ამბები -->
        <?php if (!empty($regular)): ?>
        <section class="regular-news-section">
            <h2><i class="fas fa-newspaper"></i> ბოლო ახალი ამბები</h2>
            <div class="news-grid">
                <?php foreach ($regular as $news_item): ?>
                <article class="news-card">
                    <?php if ($news_item['image']): ?>
                    <div class="news-image">
                        <img src="uploads/news/<?php echo clean_input($news_item['image']); ?>" 
                             alt="<?php echo clean_input($news_item['title']); ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <h3>
                            <a href="news_detail.php?id=<?php echo $news_item['id']; ?>">
                                <?php echo clean_input($news_item['title']); ?>
                            </a>
                        </h3>
                        
                        <p><?php echo truncate_text(clean_input($news_item['content']), 100); ?></p>
                        
                        <div class="news-meta">
                            <span><i class="fas fa-user"></i> <?php echo clean_input($news_item['full_name']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo format_date($news_item['created_at']); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo $news_item['views']; ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <?php if (empty($news)): ?>
        <div class="no-news">
            <i class="fas fa-rss" style="font-size: 5rem; color: #ddd;"></i>
            <h2>ახალი ამბები არ მოიძებნა</h2>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.news-page {
    padding: 2rem 0;
}

.news-page-header {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.news-page-header h1 {
    color: #667eea;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.news-count {
    color: #999;
    font-size: 1.1rem;
}

.breaking-section,
.regular-news-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.breaking-section h2,
.regular-news-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.8rem;
}

.breaking-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 2rem;
}

.breaking-card {
    border: 3px solid #f5576c;
    animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
    0%, 100% {
        border-color: #f5576c;
    }
    50% {
        border-color: #f093fb;
    }
}

.breaking-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    animation: pulse 2s infinite;
}

.no-news {
    background: white;
    padding: 5rem 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.no-news h2 {
    color: #999;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .breaking-grid {
        grid-template-columns: 1fr;
    }
    
    .news-page-header h1 {
        font-size: 1.8rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>