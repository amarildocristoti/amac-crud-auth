<?php

namespace App\Core;

/**
 * SECURITY — funções utilitárias de segurança usadas em vários lugares
 * do projeto. Reúne aqui tudo relacionado a CSRF, sanitização e validação,
 * pra não espalhar essa lógica repetida pelos Controllers.
 */
class Security
{
    /**
     * PROTEÇÃO CSRF (Cross-Site Request Forgery)
     *
     * CSRF é um ataque em que um site malicioso tenta fazer o navegador
     * da vítima enviar uma requisição pro NOSSO site (ex: excluir a conta
     * dela) sem que ela perceba, aproveitando que ela já está logada.
     *
     * A defesa: geramos um "token" (código aleatório) único por sessão,
     * colocamos ele escondido em todo formulário, e exigimos que ele
     * volte junto quando o formulário é enviado. Um site externo não
     * tem como saber esse token, então não consegue forjar a requisição.
     *
     * Gera (ou reaproveita) o token CSRF da sessão atual.
     */
    public static function generateCsrfToken(): string
    {
        // Se ainda não existe um token guardado na sessão, cria um novo.
        if (empty($_SESSION['csrf_token'])) {
            // random_bytes(32) gera 32 bytes criptograficamente aleatórios.
            // bin2hex converte isso pra uma string de texto (hexadecimal),
            // fácil de colocar num <input type="hidden">.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Confere se o token que veio do formulário bate com o que está
     * guardado na sessão. Chamado sempre que um formulário POST é recebido.
     */
    public static function validateCsrfToken(?string $token): bool
    {
        // Se não veio token nenhum, ou não existe token salvo na sessão,
        // já reprovamos de cara.
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        // hash_equals compara duas strings de forma seguro contra
        // "timing attacks" (um tipo de ataque que mede quanto tempo
        // a comparação demora para tentar adivinhar a string certa).
        // É sempre preferível a usar "==" ou "===" para comparar tokens/senhas.
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * PROTEÇÃO XSS (Cross-Site Scripting)
     *
     * Converte caracteres especiais de HTML (como < > " ') em suas
     * "entidades" equivalentes (&lt; &gt; &quot; &#039;), impedindo
     * que um usuário mal-intencionado injete <script> malicioso no
     * nome dele, por exemplo, e esse script rode na tela de outros usuários.
     *
     * Sempre que exibimos um dado que veio do usuário na tela (numa view),
     * devemos passar por essa função (ou usar htmlspecialchars direto).
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Verifica se uma string tem formato de email válido.
     * Usa o filtro nativo do PHP, que já cobre praticamente todos os casos.
     */
    public static function isValidEmail(string $email): bool
    {
        // filter_var devolve o próprio email se for válido, ou "false" se não for.
        // Comparamos com "!== false" pra garantir que só entra aqui se for válido.
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
