<?php
session_start();
session_unset();
session_destroy();

// Redirect to login or signup page
header("Location: homepage.php");
exit;
