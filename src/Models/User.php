<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * MODEL — a camada que conversa DIRETAMENTE com o banco de dados.
 *
 * Regra de ouro do MVC: só o Model executa SQL. O Controller nunca
 * escreve queries — ele apenas chama métodos do Model, tipo
 * $userModel->find(5), sem saber (nem precisar saber) como isso
 * é feito por dentro.
 *
 * Essa classe representa a tabela "users" do banco. Cada método
 * público é uma operação possível nessa tabela (buscar, criar,
 * atualizar, contar, etc.).
 *
 * TODAS as queries aqui usam "prepared statements" (o ->prepare()
 * seguido de ->execute()) em vez de colar os valores direto na
 * string SQL. Isso é o que nos protege de SQL Injection: os valores
 * são enviados separados do comando SQL, então o banco nunca
 * interpreta um valor malicioso como parte do comando.
 */
class User
{
    /**
     * Guarda a conexão PDO (vinda do Database::getConnection())
     * para ser usada em todos os métodos desta classe.
     */
    private PDO $db;

    /**
     * Toda vez que fazemos "new User()", o construtor já pega
     * a conexão com o banco automaticamente.
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca TODOS os usuários da tabela, do mais recente pro mais antigo.
     * (Hoje em dia usamos mais o paginate() abaixo, mas esse método
     * ainda é útil pra listagens pequenas ou exports.)
     */
    public function all(): array
    {
        // ->query() é usado quando NÃO temos valores vindos do usuário
        // para inserir na query (não tem risco de SQL Injection aqui,
        // pois não há nenhuma variável dentro do SQL).
        $stmt = $this->db->query('SELECT id, name, email, created_at FROM users ORDER BY id DESC');

        // fetchAll() devolve todas as linhas do resultado como um array
        // de arrays associativos: [['id'=>1,'name'=>'Ana',...], [...], ...]
        return $stmt->fetchAll();
    }

    /**
     * Busca uma "página" de usuários (ex: os 8 primeiros, depois os
     * próximos 8, etc.), junto com informações para montar a paginação
     * na tela (total de páginas, página atual...).
     *
     * @param int $page    Número da página que o usuário quer ver (começa em 1)
     * @param int $perPage Quantos registros mostrar por página
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        // Garante que a página nunca seja menor que 1 (evita erro se
        // alguém tentar acessar ?page=0 ou ?page=-5 na URL).
        $page = max(1, $page);

        // Calcula quantos registros "pular" antes de começar a listar.
        // Página 1 => offset 0 (não pula nada)
        // Página 2 => offset = perPage (pula a primeira página inteira)
        $offset = ($page - 1) * $perPage;

        // Primeiro descobrimos quantos usuários existem NO TOTAL,
        // para saber quantas páginas existem ao todo.
        $totalStmt = $this->db->query('SELECT COUNT(*) FROM users');
        $total = (int) $totalStmt->fetchColumn(); // fetchColumn() pega só 1 valor (não uma linha inteira)

        // Aqui usamos ->prepare() porque LIMIT e OFFSET recebem valores
        // que variam (a página que o usuário escolheu).
        $stmt = $this->db->prepare(
            'SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset'
        );

        // bindValue() é necessário aqui (em vez do execute([...]) mais simples)
        // porque o MySQL exige que LIMIT/OFFSET sejam tratados explicitamente
        // como NÚMEROS INTEIROS (PDO::PARAM_INT) — se enviados como texto,
        // o MySQL recusa a query.
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Devolve tudo que a view vai precisar para montar a tabela
        // E os botões de paginação (página atual, última página, etc.)
        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
            // ceil() arredonda pra cima: se tem 17 usuários e 8 por página,
            // são 3 páginas (2 cheias + 1 com sobra), não 2.
            'lastPage'    => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Busca UM usuário pelo ID. Devolve o array do usuário, ou "false"
     * se não existir nenhum com esse ID (union type "array|false").
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');

        // O array ['id' => $id] preenche o placeholder ":id" da query
        // de forma segura — o PDO cuida de "escapar" o valor internamente.
        $stmt->execute(['id' => $id]);

        // fetch() (sem "All") devolve só UMA linha (ou false se não achar nada).
        return $stmt->fetch();
    }

    /**
     * Busca um usuário pelo email — usado no login, para conferir
     * se existe uma conta com esse email e comparar a senha.
     * Repare que aqui usamos "SELECT *" (incluindo a senha com hash),
     * diferente dos outros métodos que escondem a coluna "password".
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Cria um novo usuário no banco. Devolve o ID do usuário recém-criado.
     */
    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())'
        );

        $stmt->execute([
            'name'     => $name,
            'email'    => $email,
            // NUNCA guardamos a senha em texto puro! password_hash()
            // transforma a senha num "hash" (código embaralhado e
            // irreversível) usando o algoritmo bcrypt por padrão.
            // Mesmo que o banco vaze, ninguém consegue "descobrir" a senha original.
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // lastInsertId() devolve o ID gerado automaticamente pelo MySQL
        // (a coluna "id" é AUTO_INCREMENT) para o registro que acabamos de inserir.
        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza nome e email de um usuário existente.
     */
    public function update(int $id, string $name, string $email): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = :name, email = :email WHERE id = :id'
        );

        // execute() devolve true/false dependendo se a query rodou com sucesso.
        return $stmt->execute([
            'name'  => $name,
            'email' => $email,
            'id'    => $id,
        ]);
    }

    /**
     * Atualiza SÓ a senha de um usuário (método separado porque a senha
     * precisa passar pelo password_hash(), diferente dos outros campos).
     */
    public function updatePassword(int $id, string $password): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id'       => $id,
        ]);
    }

    /**
     * Remove um usuário do banco pelo ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Confere se já existe algum usuário cadastrado com esse email —
     * usado para impedir cadastros duplicados.
     *
     * @param string   $email    Email a verificar
     * @param int|null $ignoreId Se informado, ignora esse ID na busca
     *                           (usado na EDIÇÃO: "existe outro usuário
     *                           com esse email, que NÃO seja eu mesmo?")
     */
    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];

        // Se estamos editando um usuário (ignoreId preenchido), adicionamos
        // uma condição extra pra não considerar o PRÓPRIO usuário como
        // "duplicado" quando ele mantém o mesmo email de antes.
        if ($ignoreId) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // fetchColumn() devolve o número (contagem). Se for maior que 0,
        // já existe alguém com esse email.
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * MÉTODOS USADOS PELO DASHBOARD — todos seguem o mesmo padrão:
     * uma query de contagem (COUNT), devolvendo um número inteiro simples.
     */

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countCreatedToday(): int
    {
        // DATE(created_at) = CURDATE() compara só a parte da DATA
        // (ignorando a hora), com a data de hoje segundo o próprio MySQL.
        $stmt = $this->db->query('SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()');
        return (int) $stmt->fetchColumn();
    }

    public function countCreatedThisMonth(): int
    {
        // Compara mês E ano separadamente, pra não confundir
        // "março de 2025" com "março de 2026", por exemplo.
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())'
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca os últimos N usuários cadastrados (usado no Dashboard).
     */
    public function latest(int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
