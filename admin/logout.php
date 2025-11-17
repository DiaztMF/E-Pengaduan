<?php
require_once '../config.php';
startSession();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke login
redirect('login.php');
?>