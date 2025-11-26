<?php
/**
 * Router for PHP Built-in Server
 * Use: php -S localhost:8000 router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Get the file path
$filePath = __DIR__ . $path;

// Check if it's a directory, try index.php
if (is_dir($filePath)) {
    $filePath = rtrim($filePath, '/') . '/index.php';
}

// If file exists, serve it
if (file_exists($filePath) && is_file($filePath)) {
    // Check if it's a PHP file
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        include $filePath;
        return true;
    }
    // Let the built-in server handle static files
    return false;
}

// Try adding .php extension
$phpFile = __DIR__ . rtrim($path, '/') . '.php';
if (file_exists($phpFile)) {
    include $phpFile;
    return true;
}

// Try .html to .php redirect
if (substr($path, -5) === '.html') {
    $phpPath = substr($path, 0, -5) . '.php';
    $phpFile = __DIR__ . $phpPath;
    if (file_exists($phpFile)) {
        header('Location: ' . $phpPath, true, 301);
        return true;
    }
}

// 404 - Page not found
include __DIR__ . '/404.php';
return true;

