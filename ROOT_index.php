<?php

/**
 * Laravel 12 Application Entry Point
 *
 * This file redirects all requests to the public/ directory
 * Use this when cPanel doesn't allow changing document root
 */

// Define the public path
define('LARAVEL_PUBLIC_PATH', __DIR__ . '/public');

// Change to public directory context
$_SERVER['SCRIPT_FILENAME'] = LARAVEL_PUBLIC_PATH . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Load Laravel from public directory
require LARAVEL_PUBLIC_PATH . '/index.php';
