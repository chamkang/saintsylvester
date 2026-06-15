<?php
require_once __DIR__ . '/_auth.php';
unset($_SESSION['admin']);
session_regenerate_id(true);
header('Location: index.php');
