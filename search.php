<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$query = clean_input($_GET['q'] ?? '');
$results = [];
$total_results = 0;

if ($query) {
    $results = search_content($query, $pdo);
    $total_results = count($results['articles']) + count($results['news']) + count($results['quizzes']);
}

$page_title = 'ძიება: ' . $query;
include 'includes/header.php';
?>

<div class="container">
    <div class="search-results-page">
        <div class="search-header">
            <h1><i class="fas fa-search"></i> ძიების შედეგები</h1>
            
            <div class="search-box-page">
                <form action="search.php" method="GET">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" value="<?php echo $query; ?>" 
                               placeholder="მოძებნე..." required>
                        <button type="submit">ძიება</button>
                    </div>
                </form>
            </div>
            
            <?php if ($query): ?>
            <p class="search-info">
                ნაპოვნია <strong><?php echo $total_results; ?></strong> შედეგი მოთხოვნაზე: 
                <strong>"<?php echo $query; ?>"</strong>
            </p>
            <?php endif; ?>
        </div>
        
        <?php if (!$query): ?>
        <div class="no-search">
            <i class="fas fa-search" style="font-size: 5rem; color: #ddd;"></i>
            <h2>შეიყვანეთ საძიებო სიტყვა</h2>
        </div>
        
        <?php elseif ($total_results === 0): ?>
        <div class="no-results">
            <i class="fas fa-sad-tear" style="font-size: 5rem; color: #ddd;"></i>
            <h2>სამწუხაროდ ვერაფერი მოიძებნა</h2>
            <p>სცადეთ სხვა საძიებო სიტყვა</p>
        </div>
        
        <?php else: ?>
        
        <!-- სტატიები -->
        <?php if (!empty($results['articles'])): ?>
        <section class="search-section">
            <h2><i class="fas fa-newspaper"></i> სტატიები (<?php echo count($results['articles']); ?>)</h2>
            <div class="search-results-grid">
                <?php foreach ($results['articles'] as $article): ?>
                <div class="search-result-card">
                    <span class="result-type">სტატია</span>
                    <h3>
                        <a href="article.php?id=<?php echo $article['id']; ?>">
                            <?php echo clean_input($article['title']); ?>
                        </a>
                    </h3>
                    <p><?php echo truncate_text(clean_input($article['content']), 150); ?></p>
                    <div class="result-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo format_date($article['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- ახალი ამბები -->
        <?php if (!empty($results['news'])): ?>
        <section class="search-section">
            <h2><i class="fas fa-rss"></i> ახალი ამბები (<?php echo count($results['news']); ?>)</h2>
            <div class="search-results-grid">
                <?php foreach ($results['news'] as $news_item): ?>
                <div class="search-result-card">
                    <span class="result-type news-type">ახალი ამბები</span>
                    <h3>
                        <a href="news_detail.php?id=<?php echo $news_item['id']; ?>">
                            <?php echo clean_input($news_item['title']); ?>
                        </a>
                    </h3>
                    <p><?php echo truncate_text(clean_input($news_item['content']), 150); ?></p>
                    <div class="result-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo format_date($news_item['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- ქვიზები -->
        <?php if (!empty($results['quizzes'])): ?>
        <section class="search-section">
            <h2><i class="fas fa-question-circle"></i> ქვიზები (<?php echo count($results['quizzes']); ?>)</h2>
            <div class="search-results-grid">
                <?php foreach ($results['quizzes'] as $quiz): ?>
                <div class="search-result-card">
                    <span class="result-type quiz-type">ქვიზი</span>
                    <h3>
                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>">
                            <?php echo clean_input($quiz['title']); ?>
                        </a>
                    </h3>
                    <p><?php echo truncate_text(clean_input($quiz['content']), 150); ?></p>
                    <div class="result-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo format_date($quiz['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<style>
.search-results-page {
    padding: 2rem 0;
}

.search-header {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.search-header h1 {
    color: #667eea;
    margin-bottom: 1.5rem;
}

.search-box-page {
    max-width: 700px;
    margin: 0 auto 1.5rem;
}

.search-info {
    color: #666;
    font-size: 1.1rem;
}

.no-search,
.no-results {
    background: white;
    padding: 4rem 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.no-search h2,
.no-results h2 {
    color: #333;
    margin: 1.5rem 0 0.5rem;
}

.no-results p {
    color: #666;
}

.search-section {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.search-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-results-grid {
    display: grid;
    gap: 1.5rem;
}

.search-result-card {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    border-left: 4px solid #667eea;
    transition: all 0.3s;
}

.search-result-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.result-type {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
}

.news-type {
    background: #4facfe;
}

.quiz-type {
    background: #f5576c;
}

.search-result-card h3 {
    margin-bottom: 0.8rem;
}

.search-result-card h3 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.search-result-card h3 a:hover {
    color: #667eea;
}

.search-result-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.result-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
    color: #999;
}

.result-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>

<?php include 'includes/footer.php'; ?>