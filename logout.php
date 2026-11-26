<?php
require 'includes/functions.php';
$_SESSION = [];
session_destroy();
redirect('login.php');
