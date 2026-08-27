<?php

namespace App\Core;

/**
 * CONTROLLER BASE — toda classe Controller do projeto (UserController,
 * AuthController, etc.) HERDA desta aqui usando "extends Controller".
 *
 * Ela existe para não repetir código: em vez de cada Controller
 * reescrever "como renderizar uma view" ou "como redirecionar",
 * eles simplesmente chamam $this->view(...) ou $this->redirect(...).
 *
 * "abstract" significa que essa classe NUNCA é usada diretamente
 * (nunca fazemos "new Controller()") — ela só serve como base para outras.
 */
abstract class Controller
{
    /**
     * Renderiza (mostra na tela) um arquivo de view, passando dados para ele.
     *
     * Exemplo de uso dentro de um Controller filho:
     *   $this->view('users/index', ['users' => $listaDeUsuarios]);
     *
     * @param string $view Caminho da view dentro de src/Views/, sem ".php".
     *                     Ex: 'users/index' aponta para src/Views/users/index.php
     * @param array  $data Dados que a view vai poder usar. Cada chave do array
     *                     vira uma variável dentro da view. Ex: ['users' => $x]
     *                     faz a view enxergar uma variável $users.
     */
    protected function view(string $view, array $data = []): void
    {
        // extract() pega cada item do array $data e cria uma variável PHP
        // com esse nome. Ou seja, $data['users'] vira a variável $users,
        // disponível dentro do arquivo de view que vamos incluir abaixo.
        extract($data);

        // Monta o caminho físico completo do arquivo da view no disco.
        // __DIR__ é a pasta onde ESTE arquivo (Controller.php) está,
        // ou seja: src/Core/. Subimos um nível (..) e entramos em Views/.
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        // Proteção: se alguém passar um nome de view que não existe,
        // paramos com uma mensagem clara em vez de dar erro confuso.
        if (!file_exists($viewPath)) {
            die("View não encontrada: {$view}");
        }

        // Sempre incluímos o header (menu, abertura do HTML) antes,
        // depois a view específica da página, e por fim o footer
        // (fechamento do HTML). Isso evita repetir <html><head>...
        // em cada view — só escrevemos o conteúdo do meio.
        require __DIR__ . '/../Views/layout/header.php';
        require $viewPath;
        require __DIR__ . '/../Views/layout/footer.php';
    }

    /**
     * Redireciona o navegador para outra URL do próprio site e
     * interrompe a execução do script (exit).
     *
     * Exemplo: $this->redirect('/users'); manda o navegador para
     * http://localhost/crud-mvc/public/users
     *
     * @param string $path Caminho relativo, sempre começando com "/". Ex: '/login'
     */
    protected function redirect(string $path): void
    {
        // header('Location: ...') é o comando HTTP que diz ao navegador
        // "vá para esta outra URL". APP_URL é a constante definida no
        // config/config.php com a URL base do projeto.
        header('Location: ' . APP_URL . $path);

        // exit é OBRIGATÓRIO depois de um redirect — sem ele, o PHP
        // continuaria executando o resto do código, o que pode causar
        // bugs estranhos (a página tenta enviar conteúdo depois do redirect).
        exit;
    }

    /**
     * Pega um valor enviado pelo formulário (POST) ou pela URL (GET),
     * já removendo espaços em branco extras nas pontas (trim).
     *
     * Exemplo: $this->input('email') pega o campo "email" do formulário,
     * seja ele enviado via POST ou GET.
     *
     * @param string $key     Nome do campo do formulário/URL
     * @param mixed  $default Valor a retornar caso o campo não exista
     */
    protected function input(string $key, $default = null)
    {
        // Procura primeiro em $_POST (dados de formulário enviado),
        // depois em $_GET (parâmetros da URL, tipo ?page=2),
        // e se não achar em nenhum dos dois, usa o valor padrão.
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;

        // Se o valor for uma string, remove espaços do início/fim.
        // Se não for string (ex: null, array), devolve como está.
        return is_string($value) ? trim($value) : $value;
    }
}
