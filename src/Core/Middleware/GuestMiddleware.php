<?php

namespace App\Core\Middleware;

/**
 * GuestMiddleware é o OPOSTO do AuthMiddleware: protege rotas que só
 * fazem sentido para quem NÃO está logado (ex: /login, /register).
 *
 * Sem ele, um usuário já logado poderia acessar /login de novo e ficar
 * confuso vendo o formulário de login mesmo já estando autenticado.
 */
class GuestMiddleware
{
    public function handle(): void
    {
        // Se JÁ existe um user_id na sessão, a pessoa já está logada —
        // não faz sentido deixar ela ver a tela de login/registro de novo.
        if (!empty($_SESSION['user_id'])) {
            // Manda direto pro Dashboard (página inicial pós-login).
            header('Location: ' . APP_URL . '/');
            exit;
        }

        // Se não está logado, não faz nada — deixa o fluxo seguir
        // normalmente para o AuthController (login/registro).
    }
}
