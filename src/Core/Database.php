<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * DATABASE — responsável por abrir e entregar a conexão com o banco MySQL.
 *
 * Usa o padrão de projeto "Singleton": não importa quantas vezes chamemos
 * Database::getConnection() durante a mesma requisição, ele SEMPRE devolve
 * a MESMA conexão já aberta, em vez de abrir uma nova toda vez.
 * Isso economiza recursos (abrir conexão com banco é uma operação "cara").
 */
class Database
{
    /**
     * Aqui fica guardada a única instância de PDO da aplicação.
     * Começa como null (nenhuma conexão aberta ainda).
     * "?PDO" significa "ou é um PDO, ou é null".
     */
    private static ?PDO $instance = null;

    /**
     * Construtor PRIVADO — isso é de propósito! Impede que qualquer
     * código faça "new Database()" de fora da classe. A única forma
     * de conseguir a conexão é chamando o método estático getConnection().
     */
    private function __construct()
    {
        // Vazio de propósito.
    }

    /**
     * Método estático (chamado como Database::getConnection(), sem
     * precisar criar um objeto Database antes) que devolve a conexão PDO.
     */
    public static function getConnection(): PDO
    {
        // Se ainda não existe conexão aberta, criamos uma agora.
        // Da segunda chamada em diante, esse "if" é falso e simplesmente
        // devolvemos a conexão que já existia.
        if (self::$instance === null) {
            // DSN (Data Source Name): string que diz ao PDO COMO conectar.
            // Inclui: driver (mysql), host, porta, nome do banco e charset
            // (utf8mb4 suporta emojis e caracteres especiais corretamente).
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

            // Opções de configuração do PDO:
            $options = [
                // Faz o PDO lançar EXCEÇÕES quando algo dá errado numa query,
                // em vez de falhar silenciosamente. Isso facilita muito debugar.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Faz todo SELECT devolver arrays associativos por padrão
                // (['id' => 1, 'name' => 'Ana']) em vez de arrays numéricos.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // MUITO IMPORTANTE PARA SEGURANÇA: desliga a "emulação" de
                // prepared statements, forçando o PDO a usar os prepared
                // statements NATIVOS do MySQL. Isso torna a proteção contra
                // SQL Injection mais forte e confiável.
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // try/catch: tentamos abrir a conexão; se der erro (banco fora
            // do ar, senha errada, etc.), capturamos o erro no catch.
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // NUNCA mostramos o erro real do banco para o usuário final —
                // isso pode vazar informações sensíveis (senha, estrutura do banco).
                // Em vez disso, guardamos o erro real num log interno...
                error_log('Erro de conexão com o banco: ' . $e->getMessage());

                // ...e mostramos uma mensagem genérica e paramos a execução.
                die('Erro ao conectar com o banco de dados.');
            }
        }

        // Devolve a conexão (seja a que acabamos de criar, seja uma
        // já existente de uma chamada anterior nesta mesma requisição).
        return self::$instance;
    }

    /**
     * Impede que alguém "clone" o objeto Database (o que criaria uma
     * segunda instância "escondida"). Reforça a garantia de que só
     * existe UMA conexão por requisição.
     */
    private function __clone() {}
}
