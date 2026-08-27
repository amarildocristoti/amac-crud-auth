<?php

/**
 * Carrega as variáveis de ambiente do arquivo .env
 * e define constantes globais de configuração.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Banco de dados
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'crud_mvc');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Aplicação
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_URL', $_ENV['APP_URL'] ?? 'localhost/crud-mvc-auth/public');
define('APP_KEY', $_ENV['APP_KEY'] ?? '');

// Exibição de erros conforme ambiente
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Configuração de sessão segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Ative a linha abaixo quando estiver rodando em produção com HTTPS
// ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
