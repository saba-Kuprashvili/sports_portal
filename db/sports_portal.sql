-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 03:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sports_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('ფეხბურთი','კალათბურთი','ტენისი','ცურვა','ძალოვანი სპორტი','სხვა') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `title`, `content`, `category`, `image`, `views`, `status`, `created_at`, `updated_at`) VALUES
(9, 8, 'ქართული ფეხბურთის ახალი დასაწყისი', 'ქართული ფეხბურთი განიცდის მნიშვნელოვან ცვლილებებს. ახალგაზრდა ნიჭიერი მოთამაშეები ევროპულ კლუბებში გადადიან და ქვეყნის სახელს ამაღლებენ. ეროვნული ნაკრები ძლიერდება და ახალი მწვრთნელის ხელმძღვანელობით დიდ შედეგებს აღწევს.', 'ფეხბურთი', NULL, 160, 'published', '2026-01-15 21:43:39', '2026-01-16 13:05:18'),
(10, 9, 'კალათბურთის ჩემპიონატი დაიწყო', 'საქართველოს კალათბურთის ჩემპიონატი წარმატებით გაიხსნა. თბილისის დინამო და ბათუმის ჯიქია პირველ მატჩში შეხვდნენ.', 'კალათბურთი', NULL, 89, 'published', '2026-01-15 21:43:39', '2026-01-15 21:43:39'),
(11, 8, 'ტენისის აკადემიის გახსნა თბილისში', 'თბილისში გაიხსნა თანამედროვე ტენისის აკადემია, სადაც ევროპული სტანდარტებით მიმდინარეობს ახალგაზრდების მომზადება.', 'ტენისი', NULL, 70, 'published', '2026-01-15 21:43:39', '2026-01-16 13:04:48'),
(12, 10, 'ქართველი მცურავების წარმატება', 'ქართველმა მცურავებმა საერთაშორისო შეჯიბრებაზე შესანიშნავი შედეგები აჩვენეს.', 'ცურვა', NULL, 45, 'published', '2026-01-15 21:43:39', '2026-01-15 21:43:39'),
(13, 9, 'ძალოვანი სპორტის განვითარება', 'ძალოვანი სპორტი საქართველოში სულ უფრო პოპულარული ხდება.', 'ძალოვანი სპორტი', NULL, 113, 'published', '2026-01-15 21:43:39', '2026-01-15 21:44:09');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `article_id`, `news_id`, `content`, `created_at`) VALUES
(1, 9, 9, NULL, '...', '2026-01-16 09:21:05');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_breaking` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `user_id`, `title`, `content`, `image`, `is_breaking`, `views`, `created_at`) VALUES
(8, 8, 'ახალი სპორტული კომპლექსი აშენდება ბათუმში', 'ბათუმში იგეგმება უახლესი სპორტული კომპლექსის მშენებლობა.', NULL, 0, 237, '2026-01-15 21:43:39'),
(9, 9, 'საქართველოს კალათბურთის ნაკრები ევროპის ჩემპიონატზე', 'ქართული კალათბურთის ნაკრები მზადება მიმდინარე ევროპის ჩემპიონატისთვის.', NULL, 0, 158, '2026-01-15 21:43:39'),
(10, 10, 'ქართველი ტენისისტის ისტორიული გამარჯვება', 'ქართველმა ტენისისტმა პირველად მოიგო ATP ტურნირი.', NULL, 0, 343, '2026-01-15 21:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `difficulty` enum('მარტივი','საშუალო','რთული') DEFAULT 'საშუალო',
  `time_limit` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `user_id`, `title`, `description`, `category`, `difficulty`, `time_limit`, `created_at`) VALUES
(6, 8, 'ქართული ფეხბურთის ისტორია', 'შეამოწმე შენი ცოდნა ქართული ფეხბურთის შესახებ', 'ფეხბურთი', 'საშუალო', 0, '2026-01-15 21:43:39'),
(7, 8, 'ოლიმპიური თამაშები', 'ქვიზი ოლიმპიური თამაშების ისტორიაზე', 'სხვა', 'რთული', 0, '2026-01-15 21:43:39'),
(8, 10, 'სპორტის სახეობები', 'რამდენს იცი სხვადასხვა სპორტის შესახებ?', 'ზოგადი', 'მარტივი', 0, '2026-01-15 21:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(200) NOT NULL,
  `option_b` varchar(200) NOT NULL,
  `option_c` varchar(200) NOT NULL,
  `option_d` varchar(200) NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `points` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `points`) VALUES
(12, 6, 'რომელ წელს გახდა დინამო თბილისი ევროპის თასის ჩემპიონი?', '1979', '1980', '1981', '1982', 'C', 1),
(13, 6, 'ვინ არის ქართული ფეხბურთის ყველაზე ბომბარდირი?', 'შოთა არველაძე', 'დავით კიფიანი', 'კახა კალაძე', 'გიორგი კინკლაძე', 'A', 1),
(14, 6, 'რომელ კლუბში თამაშობდა კახა კალაძე?', 'მანჩესტერ იუნაიტედი', 'მილანი', 'ბარსელონა', 'რეალ მადრიდი', 'B', 1),
(15, 7, 'რომელ ქალაქში ჩატარდა პირველი თანამედროვე ოლიმპიადა?', 'პარიზი', 'ლონდონი', 'ათენი', 'როდოსი', 'C', 1),
(16, 7, 'რამდენი ოქროს მედალი აქვს მაიკლ ფელპსს?', '18', '23', '28', '20', 'B', 1),
(17, 8, 'რამდენი მოთამაშეა ფეხბურთის გუნდში?', '9', '10', '11', '12', 'C', 1),
(18, 8, 'რომელი ქვეყანა არის კალათბურთის სამშობლო?', 'აშშ', 'კანადა', 'ბრაზილია', 'ინგლისი', 'A', 1),
(19, 8, 'რამდენი სეტია ტენისის მატჩში?', '2', '3', '5', 'შეიძლება განსხვავებული', 'D', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `completed_at`) VALUES
(1, 9, 7, 1, 2, '2026-01-16 09:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `article_id`, `news_id`, `rating`, `created_at`) VALUES
(1, 9, 9, NULL, 5, '2026-01-16 09:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.jpg',
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `profile_image`, `role`, `created_at`, `last_login`) VALUES
(8, 'admin', 'admin@sports.ge', '$2y$10$On1kL3Y0TpayVgHd.WxFmOOiVdGWB0Vi0WMbDAgGBO5OKVmamIQ9y', 'ადმინისტრატორი', 'default.jpg', 'admin', '2026-01-15 21:43:39', '2026-01-16 13:54:58'),
(9, 'giorgi_sports', 'giorgi@example.ge', '$2y$10$On1kL3Y0TpayVgHd.WxFmOOiVdGWB0Vi0WMbDAgGBO5OKVmamIQ9y', 'გიორგი სპორტაშვილი', 'default.jpg', 'user', '2026-01-15 21:43:39', '2026-01-16 13:04:47'),
(10, 'nino_tennis', 'nino@example.ge', '$2y$10$On1kL3Y0TpayVgHd.WxFmOOiVdGWB0Vi0WMbDAgGBO5OKVmamIQ9y', 'ნინო ტენისაშვილი', 'default.jpg', 'user', '2026-01-15 21:43:39', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);
ALTER TABLE `articles` ADD FULLTEXT KEY `idx_search` (`title`,`content`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `idx_news` (`news_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_breaking` (`is_breaking`);
ALTER TABLE `news` ADD FULLTEXT KEY `idx_news_search` (`title`,`content`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_difficulty` (`difficulty`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `idx_user_quiz` (`user_id`,`quiz_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article` (`user_id`,`article_id`),
  ADD UNIQUE KEY `unique_user_news` (`user_id`,`news_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
