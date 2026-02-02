<?php
// Fat-Free Framework Application Entry Point

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Start Fat-Free Framework
$f3 = \Base::instance();

// Set configuration
$f3->set('DEBUG', 3);
$f3->set('AUTOLOAD', 'app/');
$f3->set('UI', 'app/views/');
$f3->set('BASE', '/');

// API Configuration
$f3->set('API_URL', 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea');
$f3->set('PLAYER_URL', 'https://hhjiexi.com/play/?url=');

// Load routes
require_once __DIR__ . '/../app/routes.php';

// Run the application
$f3->run();
