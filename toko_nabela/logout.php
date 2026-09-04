<?php
include 'fungsi.php';

unset($_SESSION['user']);
session_regenerate_id(true);

header('Location: index.php');
exit;
?>
