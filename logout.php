<?php
session_start();

// სესიის განადგურება
session_unset();
session_destroy();

// Cookie-ების წაშლა
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// ბრაუზერის ქეშის წაშლა
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// მთავარ გვერდზე გადამისამართება
header('Location: index.php');
exit();
?>