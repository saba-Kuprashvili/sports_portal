<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

// წაშლა
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // საკუთარი თავის წაშლა არ შეიძლება
    if ($id == $_SESSION['user_id']) {
        show_message('საკუთარი ანგარიშის წაშლა შეუძლებელია', 'error');
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            show_message('მომხმარებელი წარმატებით წაიშალა', 'success');
        }
    }
    header('Location: users.php');
    exit;
}

// როლის შეცვლა
if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $id = $_GET['toggle_role'];
    
    if ($id == $_SESSION['user_id']) {
        show_message('საკუთარი როლის შეცვლა შეუძლებელია', 'error');
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET role = CASE WHEN role = 'admin' THEN 'user' ELSE 'admin' END
            WHERE id = ?
        ");
        if ($stmt->execute([$id])) {
            show_message('როლი წარმატებით შეიცვალა', 'success');
        }
    }
    header('Location: users.php');
    exit;
}

// ახალი მომხმარებლის დამატება
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $full_name = clean_input($_POST['full_name']);
    $role = clean_input($_POST['role']);
    
    // შემოწმება
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        show_message('ასეთი მომხმარებელი უკვე არსებობს', 'error');
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $hashed_password, $full_name, $role])) {
            show_message('მომხმარებელი წარმატებით დაემატა', 'success');
        }
    }
    
    header('Location: users.php');
    exit;
}

// ყველა მომხმარებლის წამოღება
$stmt = $pdo->query("
    SELECT u.*,
    (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count,
    (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$page_title = 'მომხმარებლების მართვა';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../css/admin_style.css">
<script>document.body.classList.add('admin-page');</script>

<div class="container">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h3><i class="fas fa-cog"></i> ადმინისტრირება</h3>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-chart-line"></i> დაშბორდი</a>
                <a href="articles.php"><i class="fas fa-newspaper"></i> სტატიები</a>
                <a href="news.php"><i class="fas fa-rss"></i> ახალი ამბები</a>
                <a href="quizzes.php"><i class="fas fa-question-circle"></i> ქვიზები</a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> მომხმარებლები</a>
                <a href="comments.php"><i class="fas fa-comments"></i> კომენტარები</a>
                <a href="../index.php"><i class="fas fa-home"></i> მთავარზე დაბრუნება</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-users"></i> მომხმარებლების მართვა</h1>
                <button onclick="toggleForm()" class="admin-btn">
                    <i class="fas fa-user-plus"></i> ახალი მომხმარებელი
                </button>
            </div>
            
            <!-- მომხმარებლის დამატების ფორმა -->
            <div id="userForm" class="admin-form-container" style="display: none;">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="add_user" value="1">
                    <h2>ახალი მომხმარებლის დამატება</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> მომხმარებლის სახელი *</label>
                            <input type="text" name="username" required minlength="3">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> ელ.ფოსტა *</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> სახელი და გვარი *</label>
                            <input type="text" name="full_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> პაროლი *</label>
                            <input type="password" name="password" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user-tag"></i> როლი *</label>
                        <select name="role" required>
                            <option value="user">მომხმარებელი</option>
                            <option value="admin">ადმინისტრატორი</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="admin-btn">
                            <i class="fas fa-save"></i> დამატება
                        </button>
                        <button type="button" onclick="toggleForm()" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-times"></i> გაუქმება
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- სტატისტიკა -->
            <div class="user-stats">
                <div class="stat-box">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3><?php echo count($users); ?></h3>
                        <p>სულ მომხმარებელი</p>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-user-shield"></i>
                    <div>
                        <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h3>
                        <p>ადმინისტრატორი</p>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-user-check"></i>
                    <div>
                        <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></h3>
                        <p>რეგულარული</p>
                    </div>
                </div>
            </div>
            
            <!-- მომხმარებლების სია -->
            <div class="admin-section">
                <h2>ყველა მომხმარებელი</h2>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>მომხმარებელი</th>
                                <th>სახელი</th>
                                <th>ელ.ფოსტა</th>
                                <th>როლი</th>
                                <th>სტატიები</th>
                                <th>კომენტარები</th>
                                <th>რეგისტრაცია</th>
                                <th>ბოლო შესვლა</th>
                                <th>მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" 
                                             alt="<?php echo clean_input($user['username']); ?>"
                                             onerror="this.src='https://via.placeholder.com/40'"
                                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        <strong><?php echo clean_input($user['username']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo clean_input($user['full_name']); ?></td>
                                <td><?php echo clean_input($user['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'ადმინი' : 'მომხმარებელი'; ?>
                                    </span>
                                </td>
                                <td><i class="fas fa-newspaper"></i> <?php echo $user['article_count']; ?></td>
                                <td><i class="fas fa-comments"></i> <?php echo $user['comment_count']; ?></td>
                                <td><?php echo format_date($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                    <?php echo format_date($user['last_login']); ?>
                                    <?php else: ?>
                                    <span style="color: #999;">არასდროს</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?toggle_role=<?php echo $user['id']; ?>" 
                                       class="btn-icon" 
                                       title="როლის შეცვლა"
                                       onclick="return confirm('დარწმუნებული ხართ რომ გსურთ როლის შეცვლა?');">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                       class="btn-icon btn-delete" 
                                       title="წაშლა"
                                       onclick="return confirm('დარწმუნებული ხართ რომ გსურთ მომხმარებლის წაშლა? ყველა მისი კონტენტი წაიშლება!');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <span style="color: #999; font-size: 0.85rem;">თქვენი ანგარიში</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('userForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<style>
.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: linear-gradient(135deg, #667eea15, #764ba215);
    padding: 1.5rem;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-box i {
    font-size: 2.5rem;
    color: #667eea;
}

.stat-box h3 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 0.3rem;
}

.stat-box p {
    color: #666;
    font-size: 0.9rem;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<?php include '../includes/footer.php'; ?>