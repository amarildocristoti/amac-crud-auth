<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\User;

/**
 * CONTROLLER — a camada que RECEBE a requisição, decide o que fazer,
 * e devolve uma resposta (view ou redirect). É o "maestro": não faz
 * o trabalho pesado sozinho, mas coordena o Model (dados) e a View (tela).
 *
 * "extends Controller" significa que esta classe herda os métodos
 * úteis já prontos em src/Core/Controller.php: view(), redirect(), input().
 *
 * Cada método público aqui corresponde a UMA ação cadastrada nas rotas
 * do public/index.php, por exemplo:
 *   $router->get('/users', 'UserController@index', ...)
 * chama exatamente o método index() abaixo.
 */
class UserController extends Controller
{
    /**
     * Instância do Model User, para conversar com o banco.
     * Guardamos aqui no construtor pra não precisar criar um "new User()"
     * toda vez dentro de cada método.
     */
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * GET /users — lista os usuários cadastrados (com paginação).
     */
    public function index(): void
    {
        // Pega o número da página da URL, ex: /users?page=2
        // Se não vier nada, assume página 1.
        $page = (int) $this->input('page', 1);
        $perPage = 8;

        // Chama o Model pra buscar os dados já paginados.
        $result = $this->userModel->paginate($page, $perPage);

        // Manda tudo pra view, que vai montar a tabela e os botões de página.
        $this->view('users/index', [
            'users'       => $result['data'],
            'currentPage' => $result['currentPage'],
            'lastPage'    => $result['lastPage'],
            'total'       => $result['total'],
        ]);
    }

    /**
     * GET /users/create — mostra o FORMULÁRIO vazio para criar um usuário.
     * (Repare: esse método só MOSTRA a tela, ele não salva nada ainda.)
     */
    public function create(): void
    {
        $this->view('users/create', [
            // Gera um token CSRF novo (ou reaproveita o da sessão)
            // pra colocar num campo escondido do formulário.
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * POST /users/store — recebe os dados do formulário de criação
     * e efetivamente SALVA o novo usuário no banco.
     */
    public function store(): void
    {
        // 1) Confere o token CSRF antes de mais nada. Se não bater,
        // provavelmente a requisição não veio do nosso próprio formulário.
        if (!Security::validateCsrfToken($this->input('csrf_token'))) {
            $_SESSION['error'] = 'Token de segurança inválido.';
            $this->redirect('/users/create');
        }

        // 2) Pega os dados enviados pelo formulário.
        $name = $this->input('name');
        $email = $this->input('email');
        $password = $this->input('password');

        // 3) VALIDAÇÃO: confere se os dados fazem sentido antes de
        // gastar uma consulta no banco. Se algo estiver errado,
        // guarda uma mensagem de erro na sessão e volta pro formulário.
        if (empty($name) || !Security::isValidEmail($email) || strlen($password) < 8) {
            $_SESSION['error'] = 'Dados inválidos. Verifique nome, email e senha (mín. 8 caracteres).';
            $this->redirect('/users/create');
        }

        // 4) Confere no banco se já existe alguém com esse email.
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Este email já está em uso.';
            $this->redirect('/users/create');
        }

        // 5) Se passou por todas as validações, manda o Model criar o registro.
        $this->userModel->create($name, $email, $password);

        // 6) Mensagem de sucesso (aparece na próxima página graças à sessão)
        // e redireciona pra listagem.
        $_SESSION['success'] = 'Usuário criado com sucesso.';
        $this->redirect('/users');
    }

    /**
     * GET /users/edit/{id} — mostra o formulário de edição JÁ PREENCHIDO
     * com os dados atuais do usuário.
     *
     * @param string $id Vem automaticamente do Router, capturado do {id}
     *                   na URL (ex: /users/edit/7 → $id = '7')
     */
    public function edit(string $id): void
    {
        // Busca o usuário no banco. (int) converte a string '7' pro número 7.
        $user = $this->userModel->find((int) $id);

        // Se não encontrar (ID inválido ou já excluído), avisa e volta.
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            $this->redirect('/users');
        }

        $this->view('users/edit', [
            'user' => $user,
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * POST /users/update/{id} — recebe o formulário de edição preenchido
     * e salva as alterações.
     */
    public function update(string $id): void
    {
        $id = (int) $id;

        if (!Security::validateCsrfToken($this->input('csrf_token'))) {
            $_SESSION['error'] = 'Token de segurança inválido.';
            $this->redirect('/users/edit/' . $id);
        }

        $name = $this->input('name');
        $email = $this->input('email');

        if (empty($name) || !Security::isValidEmail($email)) {
            $_SESSION['error'] = 'Dados inválidos.';
            $this->redirect('/users/edit/' . $id);
        }

        // Passa $id como segundo parâmetro pra emailExists() ignorar
        // o PRÓPRIO usuário na checagem de duplicidade.
        if ($this->userModel->emailExists($email, $id)) {
            $_SESSION['error'] = 'Este email já está em uso por outro usuário.';
            $this->redirect('/users/edit/' . $id);
        }

        $this->userModel->update($id, $name, $email);

        $_SESSION['success'] = 'Usuário atualizado com sucesso.';
        $this->redirect('/users');
    }

    /**
     * POST /users/delete/{id} — exclui um usuário.
     * É POST (não GET) de propósito: ações que MODIFICAM dados nunca
     * devem ser feitas por um link simples (GET), pra evitar que, por
     * exemplo, um crawler do Google acabe "clicando" e apagando registros.
     */
    public function delete(string $id): void
    {
        if (!Security::validateCsrfToken($this->input('csrf_token'))) {
            $_SESSION['error'] = 'Token de segurança inválido.';
            $this->redirect('/users');
        }

        $this->userModel->delete((int) $id);

        $_SESSION['success'] = 'Usuário removido com sucesso.';
        $this->redirect('/users');
    }
}
