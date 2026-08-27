<?php

namespace App\Core\Middleware;

/**
 * MIDDLEWARE — pense nele como um "segurança na porta" que roda ANTES
 * do Controller. Ele decide se a requisição pode continuar ou se deve
 * ser barrada/redirecionada.
 *
 * O Router chama $middleware->handle() para cada middleware cadastrado
 * numa rota, ANTES de chamar o Controller. Se o handle() não fizer nada
 * (não redirecionar), o fluxo simplesmente continua normalmente.
 *
 * AuthMiddleware protege rotas que só usuários LOGADOS podem acessar
 * (ex: /users, /users/create). Usado assim nas rotas:
 *   $router->get('/users', 'UserController@index', [AuthMiddleware::class]);
 */
class AuthMiddleware
{
    public function handle(): void
    {
        // $_SESSION['user_id'] só existe se o usuário fez login com sucesso
        // (veja AuthController::login()). Se estiver vazio, a pessoa não
        // está autenticada.
        if (empty($_SESSION['user_id'])) {
            // header('Location: ...') manda o navegador para a tela de login.
            header('Location: ' . APP_URL . '/login');

            // exit é essencial: impede que o Controller original chegue
            // a ser executado depois do redirecionamento.
            exit;
        }

        // Se chegou até aqui, o usuário está logado — não fazemos nada,
        // e o Router segue o fluxo normalmente para o Controller.
    }
}
