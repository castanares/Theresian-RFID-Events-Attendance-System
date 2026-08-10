<?php
// Simple auth helper
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login($redirect = '/index.php'){
    if (empty($_SESSION['account_type'])){
        header('Location: ' . $redirect);
        exit;
    }
}

function is_admin(){
    return !empty($_SESSION['account_type']) && in_array(strtolower($_SESSION['account_type']), ['admin','superadmin','administrator']);
}
