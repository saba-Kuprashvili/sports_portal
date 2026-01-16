<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'გთხოვთ შეავსოთ ყველა ველი';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            show_message('კეთილი იყოს შენი მობრძანება!', 'success');
            header('Location: index.php');
            exit;
        } else {
            $error = 'არასწორი მომხმარებლის სახელი ან პაროლი';
        }
    }
}

$page_title = 'შესვლა';
include 'includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <h2>კეთილი იყოს შენი მობრძანება!</h2>
            <p class="auth-subtitle">შედი სპორტულ პორტალზე</p>
            
            <?php if ($error): ?>
            <div class="error-message">
                <ul>
                    <li><?php echo $error; ?></li>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> მომხმარებელი ან ელ.ფოსტა</label>
                    <input type="text" name="username" required 
                           value="<?php echo isset($username) ? $username : ''; ?>"
                           placeholder="შეიყვანეთ მომხმარებლის სახელი ან ელ.ფოსტა">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> პაროლი</label>
                    <input type="password" name="password" required 
                           placeholder="შეიყვანეთ პაროლი">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        <span>დამახსოვრება</span>
                    </label>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> შესვლა
                </button>
            </form>
            
            <div class="auth-links">
                <a href="forgot_password.php">დაგავიწყდა პაროლი?</a>
            </div>
            
            <p class="auth-switch">
                არ გაქვთ ანგარიში? <a href="register.php">რეგისტრაცია</a>
            </p>
            
            <div class="demo-credentials">
                <h4>🔐 ტესტური ანგარიშები:</h4>
                <p><strong>ადმინი:</strong> admin / admin123</p>
                <p><strong>მომხმარებელი:</strong> giorgi_sports / admin123</p>
            </div>
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

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.8rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.form-group label {
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
}

.form-group label i {
    color: #667eea;
    font-size: 1.1rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 1.3rem 1.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 1.05rem;
    transition: all 0.3s;
    background: #f8f9fa;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: white;
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #aaa;
    font-size: 0.95rem;
}

.checkbox-label {
    display: flex !important;
    flex-direction: row !important;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-weight: 500 !important;
    padding: 0.8rem 0;
}

.checkbox-label input[type="checkbox"] {
    width: 22px;
    height: 22px;
    cursor: pointer;
    accent-color: #667eea;
}

.auth-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1.3rem;
    border: none;
    border-radius: 12px;
    font-size: 1.15rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    margin-top: 0.5rem;
}

.auth-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
}

.auth-btn:active {
    transform: translateY(-1px);
}

.auth-links {
    text-align: center;
    margin-top: 1rem;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
}

.auth-links a:hover {
    text-decoration: underline;
}

.auth-switch {
    text-align: center;
    margin-top: 1.5rem;
    color: #666;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.auth-switch a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s;
}

.auth-switch a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.demo-credentials {
    margin-top: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.demo-credentials h4 {
    color: #667eea;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.demo-credentials p {
    margin: 0.8rem 0;
    font-size: 0.95rem;
    color: #555;
    line-height: 1.6;
}

.demo-credentials strong {
    color: #667eea;
}

.error-message {
    background: linear-gradient(135deg, #fee, #fdd);
    padding: 1.2rem;
    border-radius: 12px;
    border-left: 4px solid #ef4444;
    margin-bottom: 1.5rem;
}

.error-message ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-message li {
    color: #c53030;
    font-weight: 500;
}

@media (max-width: 768px) {
    .auth-box {
        padding: 2rem 1.5rem;
    }
    
    .auth-logo {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
    
    .auth-box h2 {
        font-size: 1.5rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>