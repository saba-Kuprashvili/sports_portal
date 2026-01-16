<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-trophy"></i>
                        <span><?php echo SITE_NAME; ?></span>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '../' : ''; ?>index.php"><i class="fas fa-home"></i> მთავარი</a></li>
                        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '../' : ''; ?>index.php#articles-section"><i class="fas fa-newspaper"></i> სტატიები</a></li>
                        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '../' : ''; ?>index.php#news-section"><i class="fas fa-rss"></i> ახალი ამბები</a></li>
                        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '../' : ''; ?>index.php#quizzes-section"><i class="fas fa-question-circle"></i> ქვიზები</a></li>
                        
                        <?php if (is_logged_in()): ?>
                            <li><a href="profile.php"><i class="fas fa-user"></i> პროფილი</a></li>
                            <?php if (is_admin()): ?>
                                <li><a href="admin/index.php"><i class="fas fa-cog"></i> ადმინი</a></li>
                            <?php endif; ?>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> გასვლა</a></li>
                        <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> შესვლა</a></li>
                            <li><a href="register.php"><i class="fas fa-user-plus"></i> რეგისტრაცია</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <?php display_message(); ?>