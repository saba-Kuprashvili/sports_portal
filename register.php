<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = clean_input($_POST['full_name'] ?? '');
    
    // ვალიდაცია
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'მომხმარებლის სახელი უნდა შეიცავდეს მინიმუმ 3 სიმბოლოს';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'გთხოვთ შეიყვანოთ სწორი ელ.ფოსტა';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'პაროლი უნდა შეიცავდეს მინიმუმ 6 სიმბოლოს';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'პაროლები არ ემთხვევა';
    }
    
    if (empty($full_name)) {
        $errors[] = 'გთხოვთ შეიყვანოთ სახელი და გვარი';
    }
    
    // შემოწმება - არსებობს თუ არა მომხმარებელი
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $errors[] = 'ასეთი მომხმარებელი უკვე არსებობს';
        }
    }
    
    // რეგისტრაცია
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = 'user';
            
            show_message('წარმატებით დარეგისტრირდით!', 'success');
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'რეგისტრაცია ვერ მოხერხდა. სცადეთ თავიდან.';
        }
    }
}

$page_title = 'რეგისტრაცია';
include 'includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>გახდი ჩვენი ნაწილი!</h2>
            <p class="auth-subtitle">შექმენი ანგარიში სპორტულ პორტალზე</p>
            
            <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> მომხმარებლის სახელი</label>
                    <input type="text" name="username" required 
                           value="<?php echo isset($username) ? $username : ''; ?>"
                           placeholder="შეიყვანეთ მომხმარებლის სახელი">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> ელ.ფოსტა</label>
                    <input type="email" name="email" required 
                           value="<?php echo isset($email) ? $email : ''; ?>"
                           placeholder="example@mail.com">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> სახელი და გვარი</label>
                    <input type="text" name="full_name" required 
                           value="<?php echo isset($full_name) ? $full_name : ''; ?>"
                           placeholder="გიორგი გიორგაძე">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> პაროლი</label>
                    <input type="password" name="password" required 
                           placeholder="მინიმუმ 6 სიმბოლო">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> პაროლის დადასტურება</label>
                    <input type="password" name="confirm_password" required 
                           placeholder="გაიმეორეთ პაროლი">
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> რეგისტრაცია
                </button>
            </form>
            
            <p class="auth-switch">
                უკვე გაქვთ ანგარიში? <a href="login.php">შესვლა</a>
            </p>
        </div>
    </div>
</div>

<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.auth-page-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
}

.auth-container {
    width: 100%;
    max-width: 520px;
    padding: 0 1rem;
}

.auth-box {
    background: rgba(255, 255, 255, 0.98);
    padding: 3rem;
    border-radius: 30px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-logo {
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
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.auth-box h2 {
    text-align: center;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
}

.auth-subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 2rem;
    font-size: 1rem;
}
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 2rem 0;
}

.auth-box {
    background: white;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 500px;
}

.auth-box h2 {
    text-align: center;
    color: #667eea;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.auth-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1rem;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.auth-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.auth-switch {
    text-align: center;
    margin-top: 1.5rem;
    color: #666;
}

.auth-switch a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

.auth-switch a:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; ?>