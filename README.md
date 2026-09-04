
⚠️ **Portfolio Project** ⚠️ 

# CRUD PHP PDO 

Aplicação web desenvolvida em PHP utilizando arquitetura MVC,
PDO e MySQL, com autenticação, gerenciamento de usuários,
controle de acesso e mecanismos de segurança.

> Projeto desenvolvido para portfólio, demonstração e avaliação técnica.

## Tecnologias utilizadas

### Backend

- PHP 8+
- Arquitetura MVC
- PDO
- Composer

### Banco de dados

- MySQL
- SQL

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap 5

### Versionamento

- Git
- GitHub

## Sobre o projeto

CRUD PHP PDO é uma aplicação web desenvolvida com PHP puro,
organizada segundo o padrão MVC e utilizando PDO para acesso ao
banco de dados.

O projeto foi desenvolvido com foco em:

- organização arquitetural;
- separação de responsabilidades;
- segurança;
- autenticação;
- controle de acesso;
- persistência de dados;
- boas práticas de desenvolvimento;
- versionamento com Git.
- paginação a partir de 8 registos por páginas

## Principais funcionalidades

- Autenticação de usuários
- Registro de usuários
- Login e logout
- Controle de acesso
- Dashboard
- Gerenciamento de usuários
- CRUD de usuários
- Validação de dados
- Proteção CSRF
- Proteção contra SQL Injection através de PDO
- Rotas organizadas
- Configuração por variáveis de ambiente
- Estrutura preparada para expansão modular

## Estrutura de pastas

```
crud-mvc-auth/
├── config/
│   └── config.php          # Configurações gerais e carregamento do .env
├── public/
│   ├── index.php           # Front controller (ponto de entrada)
│   └── .htaccess
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── UserController.php
│   ├── Core/
│   │   ├── Controller.php      # Controller base (view, redirect, input)
│   │   ├── Database.php        # Conexão PDO (Singleton)
│   │   ├── Router.php          # Roteador simples
│   │   ├── Security.php        # CSRF e sanitização
│   │   └── Middleware/
│   │       ├── AuthMiddleware.php
│   │       └── GuestMiddleware.php
│   ├── Models/
│   │   └── User.php            # CRUD de usuários com prepared statements
│   └── Views/
│       ├── auth/
│       ├── users/
│       └── layout/
├── .env.example
├── .gitignore
├── .htaccess
├── composer.json
├── database.sql
├── LICENSE.sql
└── README.md
```

## Instalação

  Clone o repositorio
  ```
  git clone https://github.com/amarildocristoti/amac-php-crud.git
  ```

  Entra na Pasta
  ```
  cd amac-php-crud
  ```

1. Copie `.env.example` para `.env` e ajuste as credenciais do banco:
   ```
   cp .env.example .env
   ```

2. Instale as dependências:
   ```
   composer install
   ```

3. Crie o banco de dados executando o script ou execute o database na pasta do projeto no PhpMyAdmin:
   ```
   mysql -u root -p < database.sql 
   ```

4. Suba um servidor local apontando para a pasta `public/`:
   ```
   php -S localhost:8000 -t public
   ```

5. Acesse `http://localhost/pasta_do_projeto/public/create` para criar sua primeira conta.


## Fluxo de utilização

```text
Acesso à aplicação
        │
        ▼
    Cadastro
        │
        ▼
      Login
        │
        ▼
    Dashboard
        │
        ▼
Gerenciamento de usuários
        │
        ├── Criar
        ├── Listar
        ├── Editar
        └── Excluir
        │
        ▼
      Logout
``` 

## Segurança implementada

- **Senhas**: hash com `password_hash()` (bcrypt) — nunca em texto puro
- **SQL Injection**: todas as queries usam prepared statements (PDO com `EMULATE_PREPARES` desativado)
- **CSRF**: token único por sessão, validado em todo formulário POST
- **XSS**: toda saída de dados do usuário passa por `htmlspecialchars()`
- **Sessão**: `session_regenerate_id()` no login (evita fixação de sessão), cookies `HttpOnly` e `SameSite=Lax`
- **Autorização**: middlewares `AuthMiddleware` (rotas protegidas) e `GuestMiddleware` (rotas só para visitantes)
- **Erros**: mensagens genéricas de login (não revela se o email existe)


