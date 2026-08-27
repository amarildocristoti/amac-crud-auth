<?php

/**
 * FRONT CONTROLLER — este é o ÚNICO ponto de entrada de toda a aplicação.
 * Graças ao .htaccess (que redireciona tudo pra cá), TODA requisição
 * HTTP — seja "/login", "/users/edit/5", "/qualquer-coisa" — passa
 * primeiro por este arquivo antes de chegar em qualquer outro lugar.
 *
 * O papel dele é simples:
 *   1) Carregar a configuração da aplicação
 *   2) Criar o Router e cadastrar TODAS as rotas existentes
 *   3) Mandar o Router decidir o que fazer com a requisição atual
 */

// Carrega config/config.php, que por sua vez:
// - lê o arquivo .env (credenciais do banco, URL da app...)
// - define as constantes DB_HOST, DB_NAME, APP_URL, etc.
// - inicia a sessão PHP (session_start())
require_once __DIR__ . '/../config/config.php';

// "use" é como um "apelido" — em vez de escrever
// App\Core\Router toda vez, podemos escrever só Router.
use App\Core\Router;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\GuestMiddleware;

// Cria o roteador, que vai guardar o "mapa" de rotas.
$router = new Router();

// ===================================================================
// ROTAS DE AUTENTICAÇÃO
// GuestMiddleware protege essas rotas para que só quem NÃO está
// logado consiga acessá-las (se já estiver logado, é redirecionado).
// ===================================================================

// Mostra a TELA (formulário) de login.
$router->get('/login', 'AuthController@showLogin', [GuestMiddleware::class]);

// Recebe o formulário de login PREENCHIDO e processa (verifica senha etc.)
$router->post('/login', 'AuthController@login', [GuestMiddleware::class]);

// Mostra a TELA de cadastro.
$router->get('/register', 'AuthController@showRegister', [GuestMiddleware::class]);

// Recebe o formulário de cadastro preenchido.
$router->post('/register', 'AuthController@register', [GuestMiddleware::class]);

// Logout não precisa de middleware algum — funciona tanto logado
// quanto (por segurança) mesmo se por algum motivo já não estiver.
$router->get('/logout', 'AuthController@logout');

// ===================================================================
// ROTAS DO CRUD DE USUÁRIOS
// AuthMiddleware protege essas rotas: só quem ESTÁ logado acessa.
// Repare no padrão REST-like: mesma "entidade" (users), verbos
// diferentes (GET pra ver, POST pra alterar).
// ===================================================================

$router->get('/users', 'UserController@index', [AuthMiddleware::class]);               // Listagem
$router->get('/users/create', 'UserController@create', [AuthMiddleware::class]);        // Form de criação
$router->post('/users/store', 'UserController@store', [AuthMiddleware::class]);         // Salva criação
$router->get('/users/edit/{id}', 'UserController@edit', [AuthMiddleware::class]);       // Form de edição
$router->post('/users/update/{id}', 'UserController@update', [AuthMiddleware::class]);  // Salva edição
$router->post('/users/delete/{id}', 'UserController@delete', [AuthMiddleware::class]);  // Exclui

// ===================================================================
// ROTA INICIAL (Dashboard)
// ===================================================================
$router->get('/', 'DashboardController@index', [AuthMiddleware::class]);

// ===================================================================
// ÚLTIMA LINHA — SEMPRE por último, depois de cadastrar TODAS as rotas.
// Aqui entregamos pro Router a URL e o método HTTP da requisição
// ATUAL, pra ele descobrir qual das rotas acima deve responder.
// ===================================================================
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
