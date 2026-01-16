<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// ფილტრი
$difficulty = $_GET['difficulty'] ?? '';

// ქვიზების წამოღება
$query = "
    SELECT q.*, u.username, u.full_name,
    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
    (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id) as attempts
    FROM quizzes q
    JOIN users u ON q.user_id = u.id
";

if ($difficulty) {
    $query .= " WHERE q.difficulty = :difficulty";
}

$query .= " ORDER BY q.created_at DESC";

$stmt = $pdo->prepare($query);

if ($difficulty) {
    $stmt->bindParam(':difficulty', $difficulty);
}

$stmt->execute();
$quizzes = $stmt->fetchAll();

// ტოპ ქვიზები (ყველაზე პოპულარული)
$stmt = $pdo->query("
    SELECT q.*, 
    (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id) as attempts,
    (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
    FROM quizzes q
    ORDER BY attempts DESC
    LIMIT 5
");
$top_quizzes = $stmt->fetchAll();

$page_title = 'ყველა ქვიზი';
include 'includes/header.php';
?>

<div class="container">
    <div class="quizzes-page">
        <aside class="quizzes-sidebar">
            <div class="sidebar-widget">
                <h3><i class="fas fa-filter"></i> სირთულე</h3>
                <div class="difficulty-list">
                    <a href="quizzes.php" class="difficulty-item <?php echo !$difficulty ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> ყველა
                    </a>
                    <a href="quizzes.php?difficulty=მარტივი" 
                       class="difficulty-item easy-item <?php echo $difficulty === 'მარტივი' ? 'active' : ''; ?>">
                        <i class="fas fa-smile"></i> მარტივი
                    </a>
                    <a href="quizzes.php?difficulty=საშუალო" 
                       class="difficulty-item medium-item <?php echo $difficulty === 'საშუალო' ? 'active' : ''; ?>">
                        <i class="fas fa-meh"></i> საშუალო
                    </a>
                    <a href="quizzes.php?difficulty=რთული" 
                       class="difficulty-item hard-item <?php echo $difficulty === 'რთული' ? 'active' : ''; ?>">
                        <i class="fas fa-brain"></i> რთული
                    </a>
                </div>
            </div>
            
            <div class="sidebar-widget">
                <h3><i class="fas fa-trophy"></i> პოპულარული ქვიზები</h3>
                <div class="popular-quizzes">
                    <?php foreach ($top_quizzes as $top): ?>
                    <a href="quiz.php?id=<?php echo $top['id']; ?>" class="popular-quiz-item">
                        <div class="quiz-icon-small">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div>
                            <h4><?php echo clean_input(truncate_text($top['title'], 35)); ?></h4>
                            <span><i class="fas fa-users"></i> <?php echo $top['attempts']; ?> მცდელობა</span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
        
        <main class="quizzes-main">
            <div class="quizzes-header">
                <h1><i class="fas fa-question-circle"></i> 
                    <?php if ($difficulty): ?>
                        <?php echo $difficulty; ?> ქვიზები
                    <?php else: ?>
                        ყველა ქვიზი
                    <?php endif; ?>
                </h1>
                <p class="quizzes-count">ნაპოვნია <?php echo count($quizzes); ?> ქვიზი</p>
            </div>
            
            <?php if (empty($quizzes)): ?>
            <div class="no-quizzes">
                <i class="fas fa-question-circle" style="font-size: 5rem; color: #ddd;"></i>
                <h2>ქვიზები არ მოიძებნა</h2>
            </div>
            <?php else: ?>
            <div class="quizzes-grid">
                <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card-large">
                    <div class="quiz-icon-large">
                        <i class="fas fa-brain"></i>
                    </div>
                    
                    <h3><?php echo clean_input($quiz['title']); ?></h3>
                    
                    <?php if ($quiz['description']): ?>
                    <p class="quiz-description"><?php echo truncate_text(clean_input($quiz['description']), 100); ?></p>
                    <?php endif; ?>
                    
                    <div class="quiz-info-large">
                        <span class="quiz-difficulty-badge <?php echo strtolower($quiz['difficulty']); ?>">
                            <?php echo $quiz['difficulty']; ?>
                        </span>
                        <span><i class="fas fa-question"></i> <?php echo $quiz['question_count']; ?> კითხვა</span>
                        <span><i class="fas fa-users"></i> <?php echo $quiz['attempts']; ?> მცდელობა</span>
                    </div>
                    
                    <div class="quiz-footer">
                        <span class="quiz-author">
                            <i class="fas fa-user"></i> <?php echo clean_input($quiz['full_name']); ?>
                        </span>
                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-start-btn-large">
                            დაწყება <i class="fas fa-play"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.quizzes-page {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.quizzes-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.difficulty-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.difficulty-item {
    padding: 1rem;
    border-radius: 10px;
    text-decoration: none;
    color: #666;
    background: #f8f9fa;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.difficulty-item:hover,
.difficulty-item.active {
    transform: translateX(5px);
}

.easy-item:hover,
.easy-item.active {
    background: #4ade8015;
    color: #4ade80;
}

.medium-item:hover,
.medium-item.active {
    background: #fbbf2415;
    color: #fbbf24;
}

.hard-item:hover,
.hard-item.active {
    background: #ef444415;
    color: #ef4444;
}

.popular-quizzes {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.popular-quiz-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s;
}

.popular-quiz-item:hover {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    transform: translateX(3px);
}

.quiz-icon-small {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.popular-quiz-item h4 {
    color: #333;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.popular-quiz-item span {
    color: #999;
    font-size: 0.8rem;
}

.quizzes-main {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.quizzes-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #667eea;
}

.quizzes-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.quizzes-count {
    color: #999;
    font-size: 0.9rem;
}

.no-quizzes {
    text-align: center;
    padding: 5rem 2rem;
}

.no-quizzes h2 {
    color: #999;
    margin-top: 1rem;
}

.quizzes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.quiz-card-large {
    background: linear-gradient(135deg, #667eea05, #764ba205);
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.quiz-card-large:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.quiz-icon-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2.5rem;
}

.quiz-card-large h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.quiz-description {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.quiz-info-large {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: #666;
}

.quiz-info-large span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quiz-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.quiz-author {
    color: #999;
    font-size: 0.85rem;
}

.quiz-start-btn-large {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.quiz-start-btn-large:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

@media (max-width: 968px) {
    .quizzes-page {
        grid-template-columns: 1fr;
    }
    
    .quizzes-sidebar {
        position: static;
    }
}
</style>

<?php include 'includes/footer.php'; ?>