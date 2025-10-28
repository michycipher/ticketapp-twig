<?php

declare(strict_types=1);
session_start(); // start session once, globally

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();

// Twig setup
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

// Basic router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// helpers
function base_path($p='') { return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . ($p ? "/$p" : $p); }
function redirect($path) { header("Location: $path"); exit; }

// autoload controllers
require_once __DIR__ . '/../src/Utils/Storage.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/TicketController.php';

$auth = new AuthController($twig);
$ticket = new TicketController($twig);

# Routes
if ($uri === '/' || $uri === '/index.php') {
    echo $twig->render('landing.twig', []);
    exit;
}

if (preg_match('#^/auth/register$#', $uri)) {
    $auth->register();
    exit;
}
if (preg_match('#^/auth/login$#', $uri)) {
    $auth->login();
    exit;
}
if (preg_match('#^/auth/logout$#', $uri)) {
    $auth->logout();
    exit;
}

/* Protected pages check server-side cookie token */
if (preg_match('#^/dashboard$#', $uri)) {
    $auth->protect();
    $ticket->dashboard();
    exit;
}

if (preg_match('#^/tickets$#', $uri)) {
    $auth->protect();
    $ticket->list();
    exit;
}
if (preg_match('#^/tickets/create$#', $uri)) {
    $auth->protect();
    $ticket->create();
    exit;
}
if (preg_match('#^/tickets/edit/([0-9]+)$#', $uri, $m)) {
    $auth->protect();
    $ticket->edit((int)$m[1]);
    exit;
}
if (preg_match('#^/tickets/delete/([0-9]+)$#', $uri, $m)) {
    $auth->protect();
    $ticket->delete((int)$m[1]);
    exit;
}

/* static assets served directly by PHP dev server if needed */
$static = __DIR__ . $uri;
if (file_exists($static) && !is_dir($static)) {
    return false; // let the server handle it
}

http_response_code(404);
echo $twig->render('layout.twig', ['content' => '<h1>404 Not Found</h1>']);
