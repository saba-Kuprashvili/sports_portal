<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// სტატიების მოძიება
$stmt = $pdo->query("
    SELECT a.*, u.username, u.full_name 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'published'
    ORDER BY a.created_at DESC
    LIMIT 6
");
$articles = $stmt->fetchAll();

// ახალი ამბების მოძიება
$stmt = $pdo->query("
    SELECT n.*, u.username 
    FROM news n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.is_breaking DESC, n.created_at DESC
    LIMIT 4
");
$news = $stmt->fetchAll();

// ქვიზების მოძიება
$stmt = $pdo->query("
    SELECT q.*, u.username,
    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
    FROM quizzes q
    JOIN users u ON q.user_id = u.id
    ORDER BY q.created_at DESC
    LIMIT 4
");
$quizzes = $stmt->fetchAll();

// პოპულარული სტატიები
$popular = get_popular_articles($pdo, 3);

$page_title = 'მთავარი';
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1 class="hero-title">
            <i class="fas fa-trophy"></i>
            სპორტული პორტალი
        </h1>
        <p class="hero-subtitle">საქართველოს #1 სპორტული პლატფორმა</p>
        
        <div class="search-box">
            <form action="search.php" method="GET">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" placeholder="მოძებნე სტატიები, ახალი ამბები, ქვიზები..." required>
                    <button type="submit">ძიება</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <!-- სასწრაფო ახალი ამბები -->
    <?php 
    $breaking = array_filter($news, function($n) { return $n['is_breaking']; });
    if (!empty($breaking)): 
        $breaking_news = reset($breaking);
    ?>
    <div class="breaking-news">
        <span class="breaking-badge">
            <i class="fas fa-bolt"></i> სასწრაფო
        </span>
        <a href="news_detail.php?id=<?php echo $breaking_news['id']; ?>">
            <?php echo clean_input($breaking_news['title']); ?>
        </a>
    </div>
    <?php endif; ?>
    
    <!-- სტატიების სექცია -->
    <section class="content-section" id="articles-section">
        <div class="section-header">
            <h2><i class="fas fa-newspaper"></i> უახლესი სტატიები</h2>
            <a href="articles.php" class="view-all">ყველას ნახვა <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if ($article['image']): ?>
                <div class="article-image">
                    <img src="uploads/articles/<?php echo clean_input($article['image']); ?>" alt="<?php echo clean_input($article['title']); ?>">
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
                        <?php echo truncate_text(clean_input($article['content']), 120); ?>
                    </p>
                    
                    <div class="article-meta">
                        <span><i class="fas fa-user"></i> <?php echo clean_input($article['full_name']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo format_date($article['created_at']); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- ახალი ამბების სექცია -->
    <section class="content-section" id="news-section">
        <div class="section-header">
            <h2><i class="fas fa-rss"></i> ახალი ამბები</h2>
            <a href="news.php" class="view-all">ყველას ნახვა <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="news-grid">
            <?php foreach ($news as $news_item): ?>
            <article class="news-card">
                <?php if ($news_item['image']): ?>
                <div class="news-image">
                    <img src="uploads/news/<?php echo clean_input($news_item['image']); ?>" alt="<?php echo clean_input($news_item['title']); ?>">
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
                        <span><i class="fas fa-clock"></i> <?php echo format_date($news_item['created_at']); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $news_item['views']; ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- ქვიზების სექცია -->
    <section class="content-section" id="quizzes-section">
        <div class="section-header">
            <h2><i class="fas fa-question-circle"></i> ქვიზები</h2>
            <a href="quizzes.php" class="view-all">ყველას ნახვა <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
                <div class="quiz-icon">
                    <i class="fas fa-brain"></i>
                </div>
                
                <h3><?php echo clean_input($quiz['title']); ?></h3>
                <p><?php echo truncate_text(clean_input($quiz['description'] ?? ''), 80); ?></p>
                
                <div class="quiz-info">
                    <span class="quiz-difficulty <?php echo strtolower($quiz['difficulty']); ?>">
                        <?php echo $quiz['difficulty']; ?>
                    </span>
                    <span><i class="fas fa-question"></i> <?php echo $quiz['question_count']; ?> კითხვა</span>
                </div>
                
                <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-start-btn">
                    დაწყება <i class="fas fa-play"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- პოპულარული სტატიები (Sidebar) -->
    <aside class="sidebar-section">
        <div class="sidebar-widget">
            <h3><i class="fas fa-fire"></i> პოპულარული</h3>
            <div class="popular-list">
                <?php foreach ($popular as $pop): ?>
                <div class="popular-item">
                    <a href="article.php?id=<?php echo $pop['id']; ?>">
                        <?php echo clean_input($pop['title']); ?>
                    </a>
                    <span class="popular-views"><i class="fas fa-eye"></i> <?php echo $pop['views']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>

<?php include 'includes/footer.php'; ?>