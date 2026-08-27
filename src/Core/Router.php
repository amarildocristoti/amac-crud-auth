<?php

namespace App\Core;

/**
 * Router simples: mapeia URI + método HTTP para Controller@metodo,
 * com suporte a middlewares por rota.
 * 
 * 
 * /**
 * ROUTER — o "recepcionista" da aplicação.
 *
 * Toda requisição HTTP chega aqui depois de passar pelo public/index.php.
 * O trabalho do Router é simples: olhar para a URL que o navegador pediu
 * (ex: "/users/edit/5") e para o método HTTP (GET, POST...), e descobrir
 * QUAL Controller e QUAL método dentro dele deve tratar essa requisição.
 *
 * Ele guarda um "mapa" (array $routes) de todas as rotas cadastradas
 * no public/index.php, tipo:
 *   GET  /login  => AuthController@showLogin
 *   POST /login  => AuthController@login
 */

class Router
{
    /**
     * Aqui ficam TODAS as rotas cadastradas, organizadas assim:
     * $routes['GET']['/login']  = ['action' => 'AuthController@showLogin', 'middlewares' => [...]]
     * $routes['POST']['/login'] = ['action' => 'AuthController@login', 'middlewares' => [...]]
     *
     * Ou seja: primeiro nível é o método HTTP, segundo nível é a URI.
     */
    private array $routes = [];

    /**
     * Cadastra uma rota que responde a requisições GET.
     * GET é usado para "ver" uma página (ex: mostrar o formulário de login).
     *
     * @param string $uri         Caminho da URL, ex: '/login' ou '/users/edit/{id}'
     * @param string $action      Qual Controller@metodo vai tratar, ex: 'AuthController@showLogin'
     * @param array  $middlewares Lista de classes de middleware que rodam ANTES do controller
     */
    public function get(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    /**
     * Cadastra uma rota que responde a requisições POST.
     * POST é usado para "enviar dados" (ex: enviar o formulário de login preenchido).
     */
    public function post(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    /**
     * Método interno (private = só o próprio Router pode chamar) que
     * realmente guarda a rota dentro do array $routes.
     */
    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $this->routes[$method][$uri] = [
            'action'      => $action,
            'middlewares' => $middlewares,
        ];
    }

    /**
     * O CORAÇÃO do Router. É chamado UMA VEZ, no final do public/index.php,
     * depois que todas as rotas já foram cadastradas com ->get() e ->post().
     *
     * @param string $uri    A URL completa que veio da requisição ($_SERVER['REQUEST_URI'])
     * @param string $method O método HTTP da requisição ($_SERVER['REQUEST_METHOD'])
     */
    public function dispatch(string $uri, string $method): void
    {
        // $_SERVER['REQUEST_URI'] pode vir com query string, tipo "/users?page=2".
        // parse_url(..., PHP_URL_PATH) extrai só a parte do caminho: "/users"
        // (descarta o "?page=2", que não nos interessa aqui pra bater com a rota).
        $uri = parse_url($uri, PHP_URL_PATH);

        // ---- CÁLCULO DO "BASE PATH" (caminho da subpasta) ----
        // Se o projeto está rodando em http://localhost/crud-mvc-auth/public/,
        // o navegador manda a URI completa: "/crud-mvc-auth/public/login".
        // Mas as rotas foram cadastradas como "/login" (sem o prefixo).
        // Por isso, precisamos descobrir e remover esse prefixo automaticamente.
        //
        // $_SERVER['SCRIPT_NAME'] sempre aponta pro arquivo PHP que está
        // rodando de verdade, ex: "/crud-mvc/public/index.php".
        // dirname() pega só a pasta: "/crud-mvc-auth/public".
        // str_replace('\\', '/', ...) troca barras invertidas do Windows por normais.
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');


        // 1. PRIMEIRA TENTATIVA: Verifica se a URL atual ($uri) começa exatamente com o caminho base ($basePath).
        // Exemplo: $uri = "/crud-mvc-auth/public/login" e $basePath = "/crud-mvc-auth/public"
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {

            // Se começou com o $basePath, corta essa parte da string do início da URI.
            // strlen($basePath) pega o número de caracteres do caminho base.
            // substr() remove esses caracteres do início, deixando apenas a rota relativa.
            // Exemplo: "/crud-mvc-auth/public/login" vira "/login"
            $uri = substr($uri, strlen($basePath)); // Aqui fica /login
        } else {

            // 2. SEGUNDA TENTATIVA (FALLBACK): Executada se a URI não começou diretamente com o $basePath.
            // Isso acontece quando o usuário acessa o projeto sem especificar a subpasta inteira ou via reescrita do Apache.

            // rtrim(dirname($basePath), '/') descobre a pasta pai do $basePath e remove barras no final.
            // Exemplo: Se $basePath for "/crud-mvc-auth/public", o dirname() devolve "/crud-mvc-auth".
            $projectRoot = rtrim(dirname($basePath), '/');

            // Validações de segurança e consistência para a pasta pai ($projectRoot):
            // 1. $projectRoot !== ''  -> Garante que o caminho pai não é vazio.
            // 2. $projectRoot !== '.' -> Garante que o caminho não é o diretório atual do sistema de arquivos.
            // 3. str_starts_with($uri, $projectRoot) -> Confirma se a URL acessada começa com a pasta raiz do projeto.
            if ($projectRoot !== '' && $projectRoot !== '.' && str_starts_with($uri, $projectRoot)) {

                // Se passar nas 3 validações, corta o caminho da pasta raiz ($projectRoot) do início da URI.
                // Exemplo: Se $uri for "/crud-mvc-auth/login" e $projectRoot for "/crud-mvc-auth",
                // a URI limpa para o roteador passa a ser "/login".
                $uri = substr($uri, strlen($projectRoot));
            }
        }

        // Remove a barra final, se tiver (ex: "/users/" vira "/users"),
        // mas garante que a URI da home ("") vire "/" e não fique vazia.
        $uri = rtrim($uri, '/') ?: '/';


        // ---- PROCURA UMA ROTA QUE BATA COM A URI ----
        // $this->routes[$method] pega só as rotas do método certo (GET ou POST).
        // O "?? []" evita erro caso não exista nenhuma rota GET, por exemplo.
        foreach ($this->routes[$method] ?? [] as $route => $config) {

            // Rotas podem ter parâmetros dinâmicos, tipo '/users/edit/{id}'.
            // Aqui a gente transforma {id} numa expressão regular que aceita
            // letras, números, hífen e underscore: ([a-zA-Z0-9_-]+)
            $pattern = preg_replace('#\{[a-zA-Z]+\}#', '([a-zA-Z0-9_-]+)', $route);


            // Monta o padrão final de regex, ex: #^/users/edit/([a-zA-Z0-9_-]+)$#
            // O ^ e $ garantem que TODA a URI precisa bater, não só um pedaço.
            $pattern = '#^' . $pattern . '$#';

            // preg_match tenta casar a URI atual com esse padrão.
            // Se bater, $matches vai conter os valores capturados pelos {}
            // (ex: o "5" de /users/edit/5).
            if (preg_match($pattern, $uri, $matches)) {

                // $matches[0] é sempre a string inteira que bateu (não interessa aqui).
                // array_shift remove esse primeiro item, sobrando só os parâmetros capturados.
                array_shift($matches);

                // ---- EXECUTA OS MIDDLEWARES DA ROTA ----
                // Antes de chamar o Controller, rodamos cada middleware cadastrado
                // nessa rota (ex: AuthMiddleware, que bloqueia quem não está logado).
                // Se o middleware decidir redirecionar, ele chama exit e o código
                // abaixo nunca é executado.
                foreach ($config['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $middleware->handle();
                }

                // ---- DESCOBRE QUAL CONTROLLER E MÉTODO CHAMAR ----
                // $config['action'] é uma string tipo 'UserController@index'.
                // explode('@', ...) quebra em duas partes: 'UserController' e 'index'.
                [$controllerName, $methodName] = explode('@', $config['action']);


                // Monta o nome completo da classe, incluindo o namespace:
                // 'App\Controllers\UserController'
                $controllerClass = "App\\Controllers\\{$controllerName}";


                // Cria uma instância do Controller (ex: new UserController())
                $controller = new $controllerClass();


                // Chama o método do Controller (ex: $controller->index())
                // passando os parâmetros capturados da URL (ex: o ID do usuário).
                // call_user_func_array é como fazer $controller->$methodName(...$matches)
                call_user_func_array([$controller, $methodName], $matches);

                // Encontrou e executou a rota certa — não precisa continuar procurando.
                return;
            }
        }

        // Se o loop terminou e nenhuma rota bateu, é porque a URL não existe.
        // Respondemos com o código HTTP 404 (Não Encontrado) e uma mensagem simples.
        http_response_code(404);
        echo '404 - Página não encontrada';
    }
}
