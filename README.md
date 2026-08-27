# CRUD MVC PDO

Projeto base em PHP puro com arquitetura MVC, PDO, autenticação e segurança.
layout com Bootstrap 5.

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
├── storage/logs/
├── .env.example
├── .gitignore
├── .htaccess
├── composer.json
├── database.sql
└── README.md
```

## Instalação

1. Copie `.env.example` para `.env` e ajuste as credenciais do banco:
   ```bash
   cp .env.example .env
   ```

2. Instale as dependências:
   ```bash
   composer install
   ```

3. Crie o banco de dados executando o script ou execute o database na pasta do projeto no PhpMyAdmin:
   ```bash
   mysql -u root -p < database.sql 
   ```

4. Suba um servidor local apontando para a pasta `public/`:
   ```bash
   php -S localhost:8000 -t public
   ```

5. Acesse `http://localhost/crud-mvc-auth/public/create` para criar sua primeira conta.

## Segurança implementada

- **Senhas**: hash com `password_hash()` (bcrypt) — nunca em texto puro
- **SQL Injection**: todas as queries usam prepared statements (PDO com `EMULATE_PREPARES` desativado)
- **CSRF**: token único por sessão, validado em todo formulário POST
- **XSS**: toda saída de dados do usuário passa por `htmlspecialchars()`
- **Sessão**: `session_regenerate_id()` no login (evita fixação de sessão), cookies `HttpOnly` e `SameSite=Lax`
- **Autorização**: middlewares `AuthMiddleware` (rotas protegidas) e `GuestMiddleware` (rotas só para visitantes)
- **Erros**: mensagens genéricas de login (não revela se o email existe)


