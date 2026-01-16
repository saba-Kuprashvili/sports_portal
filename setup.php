<?php
/**
 * Setup - სპორტული პორტალი
 * გაუშვი ერთხელ: http://localhost/sports-portal/setup.php
 * შემდეგ წაშალე!
 */

require_once 'config/database.php';

$cssStyles = <<<CSS
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem;
}
.setup-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 3rem;
    border-radius: 30px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
h2 { color: #667eea; font-size: 2.5rem; margin-bottom: 0.5rem; }
h3 { color: #333; margin: 2rem 0 1rem; font-size: 1.5rem; padding-left: 10px; border-left: 4px solid #667eea; }
h4 { color: #667eea; margin-bottom: 1rem; }
hr { border: none; border-top: 2px solid #eee; margin: 2rem 0; }
.log { padding: 0.5rem; margin: 0.3rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #4ade80; }
.success-box {
    background: linear-gradient(135deg, #4ade8015, #667eea15);
    padding: 2.5rem;
    border-radius: 20px;
    border-left: 5px solid #4ade80;
    margin: 2rem 0;
}
.error-box {
    background: linear-gradient(135deg, #ef444415, #f5576c15);
    padding: 2rem;
    border-radius: 15px;
    border-left: 5px solid #ef4444;
}
ul { list-style: none; padding: 1rem 0; }
li { padding: 0.8rem 0; padding-left: 2rem; position: relative; }
li::before { content: '✓'; position: absolute; left: 0; color: #4ade80; font-weight: bold; font-size: 1.2rem; }
.btn-primary, .btn-secondary {
    display: inline-block;
    padding: 1.2rem 2.5rem;
    margin: 0.5rem 0.5rem 0.5rem 0;
    border-radius: 15px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s;
    font-size: 1.1rem;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}
.btn-secondary {
    background: #f8f9fa;
    color: #667eea;
    border: 2px solid #667eea;
}
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4); }
.btn-secondary:hover { background: #667eea; color: white; }
.warning {
    color: #ef4444;
    font-weight: 600;
    margin-top: 2rem;
    padding: 1.5rem;
    background: #ef444415;
    border-radius: 15px;
    border-left: 5px solid #ef4444;
}
.info-box {
    background: #667eea15;
    padding: 1.5rem;
    border-radius: 12px;
    margin: 1rem 0;
    border-left: 4px solid #667eea;
}
</style>
CSS;

echo "<!DOCTYPE html>
<html lang='ka'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup - სპორტული პორტალი</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    {$cssStyles}
</head>
<body>
<div class='setup-container'>";

try {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "<h2><i class='fas fa-rocket'></i> სპორტული პორტალის Setup</h2>";
    echo "<p style='color: #666; margin-bottom: 2rem;'>ინსტალაცია მიმდინარეობს...</p>";
    echo "<hr>";
    
    // ᲛᲝᲛᲮᲛᲐᲠᲔᲑᲚᲔᲑᲘ
    echo "<h3><i class='fas fa-users'></i> მომხმარებლების შექმნა</h3>";
    $pdo->exec("DELETE FROM users");
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute(['admin', 'admin@sports.ge', $password, 'ადმინისტრატორი', 'admin']);
    $admin_id = $pdo->lastInsertId();
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> <strong>admin</strong> - ადმინისტრატორი</div>";
    
    $stmt->execute(['giorgi_sports', 'giorgi@example.ge', $password, 'გიორგი სპორტაშვილი', 'user']);
    $giorgi_id = $pdo->lastInsertId();
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> <strong>giorgi_sports</strong> - მომხმარებელი</div>";
    
    $stmt->execute(['nino_tennis', 'nino@example.ge', $password, 'ნინო ტენისაშვილი', 'user']);
    $nino_id = $pdo->lastInsertId();
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> <strong>nino_tennis</strong> - მომხმარებელი</div>";
    
    // ᲡᲢᲐᲢᲘᲔᲑᲘ
    echo "<h3><i class='fas fa-newspaper'></i> სტატიების დამატება</h3>";
    $pdo->exec("DELETE FROM articles");
    
    $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, content, category, views, status) VALUES (?, ?, ?, ?, ?, 'published')");
    
    $articles = [
        [$admin_id, 'ქართული ფეხბურთის ახალი დასაწყისი', 'ქართული ფეხბურთი განიცდის მნიშვნელოვან ცვლილებებს. ახალგაზრდა ნიჭიერი მოთამაშეები ევროპულ კლუბებში გადადიან და ქვეყნის სახელს ამაღლებენ. ეროვნული ნაკრები ძლიერდება და ახალი მწვრთნელის ხელმძღვანელობით დიდ შედეგებს აღწევს.', 'ფეხბურთი', 150],
        [$giorgi_id, 'კალათბურთის ჩემპიონატი დაიწყო', 'საქართველოს კალათბურთის ჩემპიონატი წარმატებით გაიხსნა. თბილისის დინამო და ბათუმის ჯიქია პირველ მატჩში შეხვდნენ.', 'კალათბურთი', 89],
        [$admin_id, 'ტენისის აკადემიის გახსნა თბილისში', 'თბილისში გაიხსნა თანამედროვე ტენისის აკადემია, სადაც ევროპული სტანდარტებით მიმდინარეობს ახალგაზრდების მომზადება.', 'ტენისი', 67],
        [$nino_id, 'ქართველი მცურავების წარმატება', 'ქართველმა მცურავებმა საერთაშორისო შეჯიბრებაზე შესანიშნავი შედეგები აჩვენეს.', 'ცურვა', 45],
        [$giorgi_id, 'ძალოვანი სპორტის განვითარება', 'ძალოვანი სპორტი საქართველოში სულ უფრო პოპულარული ხდება.', 'ძალოვანი სპორტი', 112]
    ];
    
    foreach ($articles as $article) {
        $stmt->execute($article);
    }
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> დაემატა <strong>5 სტატია</strong></div>";
    
    // ᲐᲮᲐᲚᲘ ᲐᲛᲑᲔᲑᲘ
    echo "<h3><i class='fas fa-rss'></i> ახალი ამბების დამატება</h3>";
    $pdo->exec("DELETE FROM news");
    
    $stmt = $pdo->prepare("INSERT INTO news (user_id, title, content, is_breaking, views) VALUES (?, ?, ?, ?, ?)");
    
    $news = [
        [$admin_id, 'BREAKING: საქართველო მსოფლიო ჩემპიონატზე გავიდა!', 'ისტორიული გამარჯვება! ქართული ნაკრები პირველად გავიდა მსოფლიო ჩემპიონატის ფინალურ ტურში.', 1, 523],
        [$admin_id, 'ახალი სპორტული კომპლექსი აშენდება ბათუმში', 'ბათუმში იგეგმება უახლესი სპორტული კომპლექსის მშენებლობა.', 0, 234],
        [$giorgi_id, 'საქართველოს კალათბურთის ნაკრები ევროპის ჩემპიონატზე', 'ქართული კალათბურთის ნაკრები მზადება მიმდინარე ევროპის ჩემპიონატისთვის.', 0, 156],
        [$nino_id, 'ქართველი ტენისისტის ისტორიული გამარჯვება', 'ქართველმა ტენისისტმა პირველად მოიგო ATP ტურნირი.', 0, 342]
    ];
    
    foreach ($news as $n) {
        $stmt->execute($n);
    }
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> დაემატა <strong>4 ახალი ამბები</strong></div>";
    
    // ᲥᲕᲘᲖᲔᲑᲘ
    echo "<h3><i class='fas fa-brain'></i> ქვიზების დამატება</h3>";
    $pdo->exec("DELETE FROM quizzes");
    
    $stmt = $pdo->prepare("INSERT INTO quizzes (user_id, title, description, category, difficulty) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([$admin_id, 'ქართული ფეხბურთის ისტორია', 'შეამოწმე შენი ცოდნა ქართული ფეხბურთის შესახებ', 'ფეხბურთი', 'საშუალო']);
    $quiz1_id = $pdo->lastInsertId();
    
    $stmt->execute([$admin_id, 'ოლიმპიური თამაშები', 'ქვიზი ოლიმპიური თამაშების ისტორიაზე', 'სხვა', 'რთული']);
    $quiz2_id = $pdo->lastInsertId();
    
    $stmt->execute([$nino_id, 'სპორტის სახეობები', 'რამდენს იცი სხვადასხვა სპორტის შესახებ?', 'ზოგადი', 'მარტივი']);
    $quiz3_id = $pdo->lastInsertId();
    
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> დაემატა <strong>3 ქვიზი</strong></div>";
    
    // ᲙᲘᲗᲮᲕᲔᲑᲘ
    $pdo->exec("DELETE FROM quiz_questions");
    $stmt = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $questions = [
        [$quiz1_id, 'რომელ წელს გახდა დინამო თბილისი ევროპის თასის ჩემპიონი?', '1979', '1980', '1981', '1982', 'C', 1],
        [$quiz1_id, 'ვინ არის ქართული ფეხბურთის ყველაზე ბომბარდირი?', 'შოთა არველაძე', 'დავით კიფიანი', 'კახა კალაძე', 'გიორგი კინკლაძე', 'A', 1],
        [$quiz1_id, 'რომელ კლუბში თამაშობდა კახა კალაძე?', 'მანჩესტერ იუნაიტედი', 'მილანი', 'ბარსელონა', 'რეალ მადრიდი', 'B', 1],
        [$quiz2_id, 'რომელ ქალაქში ჩატარდა პირველი თანამედროვე ოლიმპიადა?', 'პარიზი', 'ლონდონი', 'ათენი', 'როდოსი', 'C', 1],
        [$quiz2_id, 'რამდენი ოქროს მედალი აქვს მაიკლ ფელპსს?', '18', '23', '28', '20', 'B', 1],
        [$quiz3_id, 'რამდენი მოთამაშეა ფეხბურთის გუნდში?', '9', '10', '11', '12', 'C', 1],
        [$quiz3_id, 'რომელი ქვეყანა არის კალათბურთის სამშობლო?', 'აშშ', 'კანადა', 'ბრაზილია', 'ინგლისი', 'A', 1],
        [$quiz3_id, 'რამდენი სეტია ტენისის მატჩში?', '2', '3', '5', 'შეიძლება განსხვავებული', 'D', 1]
    ];
    
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "<div class='log'><i class='fas fa-check-circle' style='color: #4ade80;'></i> დაემატა <strong>8 კითხვა</strong></div>";
    
    echo "<hr>";
    echo "<div class='success-box'>";
    echo "<h4><i class='fas fa-check-circle'></i> Setup წარმატებით დასრულდა!</h4>";
    echo "<div class='info-box'>";
    echo "<p><strong>ტესტური ანგარიშები (პაროლი: admin123):</strong></p>";
    echo "<ul style='margin-left: 1rem;'>";
    echo "<li><strong style='color: #667eea;'>admin</strong> - ადმინისტრატორი</li>";
    echo "<li><strong style='color: #667eea;'>giorgi_sports</strong> - მომხმარებელი</li>";
    echo "<li><strong style='color: #667eea;'>nino_tennis</strong> - მომხმარებელი</li>";
    echo "</ul></div>";
    echo "<p style='margin-top: 1.5rem;'><strong>რა შეიქმნა:</strong></p>";
    echo "<ul>";
    echo "<li>3 მომხმარებელი</li>";
    echo "<li>5 სტატია</li>";
    echo "<li>4 ახალი ამბები</li>";
    echo "<li>3 ქვიზი (8 კითხვით)</li>";
    echo "</ul>";
    echo "<div style='margin-top: 2rem;'>";
    echo "<a href='index.php' class='btn-primary'><i class='fas fa-home'></i> მთავარზე გადასვლა</a>";
    echo "<a href='login.php' class='btn-secondary'><i class='fas fa-sign-in-alt'></i> შესვლა</a>";
    echo "</div></div>";
    echo "<div class='warning'><i class='fas fa-exclamation-triangle'></i> <strong>მნიშვნელოვანია!</strong> ახლა წაშალე ეს ფაილი (setup.php) უსაფრთხოების მიზნით!</div>";
    
} catch (PDOException $e) {
    echo "<div class='error-box'>";
    echo "<h3><i class='fas fa-times-circle'></i> შეცდომა:</h3>";
    echo "<p><code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
    echo "</div>";
}

echo "</div></body></html>";
?>