<?php
session_start(); 
session_destroy(); 
header("Location: login.php");//redirecciono a login
?>