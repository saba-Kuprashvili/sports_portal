<?php
// უსაფრთხოების ფუნქციები
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// მომხმარებლის ავტორიზაციის შემოწმება
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// ადმინის შემოწმება
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ავტორიზაციის მოთხოვნა
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// ადმინის უფლებების მოთხოვნა
function require_admin() {
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// ფაილის ატვირთვა (დამატებითი ფუნქციონალი #1)
function upload_file($file, $folder = 'articles') {
    $upload_dir = UPLOAD_PATH . $folder . '/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'მხოლოდ სურათების ატვირთვაა შესაძლებელი'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'ფაილის ზომა არ უნდა აღემატებოდეს 5MB-ს'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'ფაილის ატვირთვა ვერ მოხერხდა'];
}

// ძიება (დამატებითი ფუნქციონალი #2)
function search_content($query, $pdo) {
    $search_term = "%{$query}%";
    $results = [];
    
    // სტატიების ძიება
    $stmt = $pdo->prepare("
        SELECT 'article' as type, id, title, content, created_at 
        FROM articles 
        WHERE (title LIKE ? OR content LIKE ?) AND status = 'published'
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $results['articles'] = $stmt->fetchAll();
    
    // ახალი ამბების ძიება
    $stmt = $pdo->prepare("
        SELECT 'news' as type, id, title, content, created_at 
        FROM news 
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $results['news'] = $stmt->fetchAll();
    
    // ქვიზების ძიება
    $stmt = $pdo->prepare("
        SELECT 'quiz' as type, id, title, description as content, created_at 
        FROM quizzes 
        WHERE title LIKE ? OR description LIKE ?
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $results['quizzes'] = $stmt->fetchAll();
    
    return $results;
}

// რეიტინგის გამოთვლა (დამატებითი ფუნქციონალი #3)
function get_average_rating($type, $id, $pdo) {
    $column = $type === 'article' ? 'article_id' : 'news_id';
    
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
        FROM ratings 
        WHERE {$column} = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// პოპულარული სტატიები
function get_popular_articles($pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.username, u.full_name 
        FROM articles a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'published'
        ORDER BY a.views DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// დროის ფორმატირება
function format_date($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'ახლახანს';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' წუთის წინ';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' საათის წინ';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' დღის წინ';
    } else {
        return date('d.m.Y', $timestamp);
    }
}

// ტექსტის შემოკლება
function truncate_text($text, $length = 150) {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

// შეტყობინებების ჩვენება
function show_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function display_message() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $class = $type === 'success' ? 'success-message' : 'error-message';
        echo "<div class='{$class}'>" . clean_input($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// CSRF დაცვა
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>