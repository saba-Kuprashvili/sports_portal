<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// სტატისტიკა
$stats = [];

// მომხმარებლები
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $stmt->fetch()['total'];

// სტატიები
$stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
$stats['articles'] = $stmt->fetch()['total'];

// ახალი ამბები
$stmt = $pdo->query("SELECT COUNT(*) as total FROM news");
$stats['news'] = $stmt->fetch()['total'];

// ქვიზები
$stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
$stats['quizzes'] = $stmt->fetch()['total'];

// კომენტარები
$stmt = $pdo->query("SELECT COUNT(*) as total FROM comments");
$stats['comments'] = $stmt->fetch()['total'];

// ბოლო სტატიები
$stmt = $pdo->query("
    SELECT a.*, u.username 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recent_articles = $stmt->fetchAll();

// ბოლო მომხმარებლები
$stmt = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_users = $stmt->fetchAll();

$page_title = 'ადმინ პანელი';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../css/admin_style.css">

<div class="container">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h3><i class="fas fa-cog"></i> ადმინისტრირება</h3>
            <nav class="admin-nav">
                <a href="index.php" class="active"><i class="fas fa-chart-line"></i> დაშბორდი</a>
                <a href="articles.php"><i class="fas fa-newspaper"></i> სტატიები</a>
                <a href="news.php"><i class="fas fa-rss"></i> ახალი ამბები</a>
                <a href="quizzes.php"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <h1>📊 დაშბორდი</h1>
            
            <!-- სტატისტიკა -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['users']; ?></h3>
                        <p>მომხმარებელი</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['articles']; ?></h3>
                        <p>სტატია</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-rss"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['news']; ?></h3>
                        <p>ახალი ამბები</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['quizzes']; ?></h3>
                        <p>ქვიზები</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['comments']; ?></h3>
                        <p>კომენტარი</p>
                    </div>
                </div>
            </div>
            
            <!-- ბოლო აქტივობები -->
            <div class="admin-section">
                <h2><i class="fas fa-clock"></i> ბოლო სტატიები</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>სათაური</th>
                                <th>კატეგორია</th>
                                <th>ავტორი</th>
                                <th>ნახვები</th>
                                <th>სტატუსი</th>
                                <th>თარიღი</th>
                                <th>მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_articles as $article): ?>
                            <tr>
                                <td>#<?php echo $article['id']; ?></td>
                                <td><?php echo clean_input(truncate_text($article['title'], 50)); ?></td>
                                <td><span class="badge"><?php echo $article['category']; ?></span></td>
                                <td><?php echo clean_input($article['username']); ?></td>
                                <td><?php echo $article['views']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $article['status']; ?>">
                                        <?php echo $article['status'] === 'published' ? 'გამოქვეყნებული' : 'დრაფტი'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($article['created_at']); ?></td>
                                <td>
                                    <a href="../article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="ნახვა">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="articles.php?edit=<?php echo $article['id']; ?>" class="btn-icon" title="რედაქტირება">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-user-plus"></i> ახალი მომხმარებლები</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>მომხმარებელი</th>
                                <th>სახელი</th>
                                <th>ელ.ფოსტა</th>
                                <th>როლი</th>
                                <th>რეგისტრაცია</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo clean_input($user['username']); ?></td>
                                <td><?php echo clean_input($user['full_name']); ?></td>
                                <td><?php echo clean_input($user['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'ადმინი' : 'მომხმარებელი'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($user['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.admin-wrapper {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.admin-sidebar {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.admin-sidebar h3 {
    color: #667eea;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.admin-nav a {
    padding: 1rem;
    color: #333;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.admin-nav a:hover,
.admin-nav a.active {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    color: #667eea;
    transform: translateX(5px);
}

.admin-content {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.admin-content h1 {
    color: #333;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.stat-info h3 {
    font-size: 2rem;
    margin-bottom: 0.3rem;
    color: #333;
}

.stat-info p {
    color: #666;
    font-size: 0.9rem;
}

.admin-section {
    margin-bottom: 2rem;
}

.admin-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
}

.admin-table-wrapper {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.admin-table th,
.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    background: #667eea;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.published {
    background: #4ade80;
    color: white;
}

.status-badge.draft {
    background: #fbbf24;
    color: white;
}

.role-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.role-badge.admin {
    background: #f5576c;
    color: white;
}

.role-badge.user {
    background: #667eea;
    color: white;
}

.btn-icon {
    color: #667eea;
    font-size: 1.1rem;
    margin: 0 0.3rem;
    transition: all 0.3s;
}

.btn-icon:hover {
    transform: scale(1.2);
    color: #764ba2;
}

@media (max-width: 968px) {
    .admin-wrapper {
        grid-template-columns: 1fr;
    }
    
    .admin-sidebar {
        position: static;
    }
}
</style>

<?php include '../includes/footer.php'; ?>