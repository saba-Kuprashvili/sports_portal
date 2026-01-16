-- მონაცემთა ბაზის შექმნა
CREATE DATABASE IF NOT EXISTS sports_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sports_portal;

-- 1. მომხმარებლების ცხრილი
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. სტატიების ცხრილი
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('ფეხბურთი', 'კალათბურთი', 'ტენისი', 'ცურვა', 'ძალოვანი სპორტი', 'სხვა') NOT NULL,
    image VARCHAR(255),
    views INT DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. ახალი ამბების ცხრილი
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    is_breaking BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_breaking (is_breaking),
    FULLTEXT idx_news_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ქვიზების ცხრილი
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    difficulty ENUM('მარტივი', 'საშუალო', 'რთული') DEFAULT 'საშუალო',
    time_limit INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_difficulty (difficulty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. ქვიზის კითხვების ცხრილი
CREATE TABLE quiz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(200) NOT NULL,
    option_b VARCHAR(200) NOT NULL,
    option_c VARCHAR(200) NOT NULL,
    option_d VARCHAR(200) NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    points INT DEFAULT 1,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. ქვიზის შედეგების ცხრილი
CREATE TABLE quiz_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user_quiz (user_id, quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. კომენტარების ცხრილი
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    article_id INT,
    news_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    INDEX idx_article (article_id),
    INDEX idx_news (news_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. რეიტინგების ცხრილი
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    article_id INT,
    news_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_article (user_id, article_id),
    UNIQUE KEY unique_user_news (user_id, news_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ტესტური მონაცემები

-- IMPORTANT: ეს არის დროებითი მონაცემები
-- რეალურ პროექტში გამოიყენე create_test_users.php ფაილი

-- ადმინ და მომხმარებლები (პაროლი: admin123)
-- ეს hash-ები არ იმუშავებს, გამოიყენე create_test_users.php
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@sports.ge', '$2y$10$dummy_hash_replace_with_real', 'ადმინისტრატორი', 'admin'),
('giorgi_sports', 'giorgi@example.ge', '$2y$10$dummy_hash_replace_with_real', 'გიორგი სპორტაშვილი', 'user'),
('nino_tennis', 'nino@example.ge', '$2y$10$dummy_hash_replace_with_real', 'ნინო ტენისაშვილი', 'user');

-- სტატიები
INSERT INTO articles (user_id, title, content, category, views) VALUES
(1, 'ქართული ფეხბურთის ახალი დასაწყისი', 'ქართული ფეხბურთი განიცდის მნიშვნელოვან ცვლილებებს. ახალგაზრდა ნიჭიერი მოთამაშეები ევროპულ კლუბებში გადადიან და ქვეყნის სახელს ამაღლებენ. ეროვნული ნაკრები ძლიერდება და ახალი მწვრთნელის ხელმძღვანელობით დიდ შედეგებს აღწევს.', 'ფეხბურთი', 150),
(2, 'კალათბურთის ჩემპიონატი დაიწყო', 'საქართველოს კალათბურთის ჩემპიონატი წარმატებით გაიხსნა. თბილისის დინამო და ბათუმის ჯიქია პირველ მატჩში შეხვდნენ მაყურებელთა დიდი ინტერესით.', 'კალათბურთი', 89),
(1, 'ტენისის აკადემიის გახსნა თბილისში', 'თბილისში გაიხსნა თანამედროვე ტენისის აკადემია, სადაც ევროპული სტანდარტებით მიმდინარეობს ახალგაზრდების მომზადება.', 'ტენისი', 67);

-- ახალი ამბები
INSERT INTO news (user_id, title, content, is_breaking, views) VALUES
(1, 'BREAKING: საქართველო მსოფლიო ჩემპიონატზე გავიდა!', 'ისტორიული გამარჯვება! ქართული ნაკრები პირველად გავიდა მსოფლიო ჩემპიონატის ფინალურ ტურში.', TRUE, 523),
(1, 'ახალი სპორტული კომპლექსი აშენდება ბათუმში', 'ბათუმში იგეგმება უახლესი სპორტული კომპლექსის მშენებლობა, რომელიც 2025 წლის ბოლოსთვის დასრულდება.', FALSE, 234);

-- ქვიზები
INSERT INTO quizzes (user_id, title, description, category, difficulty) VALUES
(1, 'ქართული ფეხბურთის ისტორია', 'შეამოწმე შენი ცოდნა ქართული ფეხბურთის შესახებ', 'ფეხბურთი', 'საშუალო'),
(1, 'ოლიმპიური თამაშები', 'ქვიზი ოლიმპიური თამაშების ისტორიაზე', 'სხვა', 'რთული');

-- ქვიზის კითხვები
INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(1, 'რომელ წელს გახდა დინამო თბილისი ევროპის თასის ჩემპიონი?', '1979', '1980', '1981', '1982', 'C'),
(1, 'ვინ არის ქართული ფეხბურთის ყველაზე ბომბარდირი?', 'შოთა არველაძე', 'დავით კიფიანი', 'კახა კალაძე', 'გიორგი კინკლაძე', 'A'),
(2, 'რომელ ქალაქში ჩატარდა პირველი თანამედროვე ოლიმპიადა?', 'პარიზი', 'ლონდონი', 'ათენი', 'როდოსი', 'C');