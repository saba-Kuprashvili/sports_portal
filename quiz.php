<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? 0;

// ქვიზის წამოღება
$stmt = $pdo->prepare("
    SELECT q.*, u.username, u.full_name 
    FROM quizzes q
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ?
");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: index.php');
    exit;
}

// კითხვების წამოღება
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$id]);
$questions = $stmt->fetchAll();

// შედეგის დამუშავება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    require_login();
    
    $score = 0;
    $total = count($questions);
    
    foreach ($questions as $question) {
        $user_answer = $_POST['question_' . $question['id']] ?? '';
        if ($user_answer === $question['correct_answer']) {
            $score += $question['points'];
        }
    }
    
    // შედეგის შენახვა
    $stmt = $pdo->prepare("
        INSERT INTO quiz_results (user_id, quiz_id, score, total_questions) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $id, $score, $total]);
    
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $total,
        'percentage' => round(($score / array_sum(array_column($questions, 'points'))) * 100)
    ];
    
    header("Location: quiz_result.php?id=$id");
    exit;
}

// საუკეთესო შედეგები
$stmt = $pdo->prepare("
    SELECT qr.*, u.username, u.full_name 
    FROM quiz_results qr
    JOIN users u ON qr.user_id = u.id
    WHERE qr.quiz_id = ?
    ORDER BY qr.score DESC
    LIMIT 10
");
$stmt->execute([$id]);
$leaderboard = $stmt->fetchAll();

$page_title = $quiz['title'];
include 'includes/header.php';
?>

<div class="container">
    <div class="quiz-container">
        <div class="quiz-header">
            <div class="quiz-info-card">
                <h1><i class="fas fa-brain"></i> <?php echo clean_input($quiz['title']); ?></h1>
                
                <?php if ($quiz['description']): ?>
                <p class="quiz-description"><?php echo clean_input($quiz['description']); ?></p>
                <?php endif; ?>
                
                <div class="quiz-meta">
                    <span class="quiz-difficulty-badge <?php echo strtolower($quiz['difficulty']); ?>">
                        <i class="fas fa-signal"></i> <?php echo $quiz['difficulty']; ?>
                    </span>
                    <span><i class="fas fa-question"></i> <?php echo count($questions); ?> კითხვა</span>
                    <?php if ($quiz['time_limit']): ?>
                    <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?> წუთი</span>
                    <?php endif; ?>
                    <span><i class="fas fa-user"></i> <?php echo clean_input($quiz['full_name']); ?></span>
                </div>
            </div>
        </div>
        
        <?php if (is_logged_in()): ?>
        <form method="POST" class="quiz-form" id="quizForm">
            <input type="hidden" name="submit_quiz" value="1">
            
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
                <div class="question-number">კითხვა <?php echo $index + 1; ?>/<?php echo count($questions); ?></div>
                <h3 class="question-text"><?php echo clean_input($question['question']); ?></h3>
                
                <div class="options-list">
                    <?php 
                    $options = ['A' => $question['option_a'], 'B' => $question['option_b'], 
                                'C' => $question['option_c'], 'D' => $question['option_d']];
                    foreach ($options as $key => $value): 
                    ?>
                    <label class="option-item">
                        <input type="radio" name="question_<?php echo $question['id']; ?>" 
                               value="<?php echo $key; ?>" required>
                        <span class="option-label">
                            <span class="option-letter"><?php echo $key; ?></span>
                            <span class="option-text"><?php echo clean_input($value); ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="quiz-submit">
                <button type="submit" class="quiz-submit-btn">
                    <i class="fas fa-check"></i> პასუხების გაგზავნა
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="login-required">
            <i class="fas fa-lock" style="font-size: 3rem; color: #667eea;"></i>
            <h2>ქვიზის გასავლელად საჭიროა ავტორიზაცია</h2>
            <a href="login.php" class="login-btn">შესვლა</a>
        </div>
        <?php endif; ?>
        
        <!-- ლიდერბორდი -->
        <?php if (!empty($leaderboard)): ?>
        <div class="leaderboard-section">
            <h2><i class="fas fa-trophy"></i> საუკეთესო შედეგები</h2>
            <div class="leaderboard-list">
                <?php foreach ($leaderboard as $rank => $result): ?>
                <div class="leaderboard-item rank-<?php echo $rank + 1; ?>">
                    <span class="rank-number">#<?php echo $rank + 1; ?></span>
                    <span class="player-name"><?php echo clean_input($result['full_name']); ?></span>
                    <span class="player-score">
                        <?php echo $result['score']; ?>/<?php echo array_sum(array_column($questions, 'points')); ?>
                    </span>
                    <?php if ($rank === 0): ?>
                    <i class="fas fa-crown" style="color: #fbbf24;"></i>
                    <?php elseif ($rank === 1): ?>
                    <i class="fas fa-medal" style="color: #c0c0c0;"></i>
                    <?php elseif ($rank === 2): ?>
                    <i class="fas fa-medal" style="color: #cd7f32;"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.quiz-container {
    max-width: 900px;
    margin: 2rem auto;
}

.quiz-header {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.quiz-info-card h1 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 2rem;
}

.quiz-description {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.quiz-meta {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    font-size: 0.95rem;
    color: #666;
}

.quiz-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quiz-difficulty-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
}

.quiz-difficulty-badge.მარტივი {
    background: #4ade80;
    color: white;
}

.quiz-difficulty-badge.საშუალო {
    background: #fbbf24;
    color: white;
}

.quiz-difficulty-badge.რთული {
    background: #ef4444;
    color: white;
}

.quiz-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.question-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.question-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.question-number {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.question-text {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    line-height: 1.4;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.option-item {
    cursor: pointer;
}

.option-item input {
    display: none;
}

.option-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem;
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 15px;
    transition: all 0.3s;
}

.option-item:hover .option-label {
    background: #667eea15;
    border-color: #667eea;
}

.option-item input:checked + .option-label {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    border-color: #667eea;
}

.option-letter {
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.option-item input:checked + .option-label .option-letter {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.option-text {
    color: #333;
    font-size: 1.05rem;
}

.quiz-submit {
    text-align: center;
    margin-top: 1rem;
}

.quiz-submit-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1.2rem 3rem;
    border: none;
    border-radius: 30px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.quiz-submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
}

.login-required {
    background: white;
    border-radius: 20px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.login-required h2 {
    color: #333;
    margin: 1.5rem 0;
}

.login-btn {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1rem 2.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.3s;
}

.login-btn:hover {
    transform: scale(1.05);
}

.leaderboard-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-top: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.leaderboard-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    text-align: center;
}

.leaderboard-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.2rem;
    background: #f8f9fa;
    border-radius: 15px;
    transition: all 0.3s;
}

.leaderboard-item:hover {
    transform: translateX(5px);
    background: #667eea15;
}

.leaderboard-item.rank-1 {
    background: linear-gradient(135deg, #fbbf2415, #f5576c15);
    border-left: 4px solid #fbbf24;
}

.rank-number {
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.leaderboard-item.rank-1 .rank-number {
    background: linear-gradient(135deg, #fbbf24, #f5576c);
}

.player-name {
    flex: 1;
    font-weight: 600;
    color: #333;
}

.player-score {
    font-weight: bold;
    color: #667eea;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .quiz-header {
        padding: 2rem 1.5rem;
    }
    
    .quiz-info-card h1 {
        font-size: 1.5rem;
    }
    
    .quiz-meta {
        flex-direction: column;
        align-items: center;
    }
    
    .question-card {
        padding: 1.5rem;
    }
    
    .question-text {
        font-size: 1.1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>