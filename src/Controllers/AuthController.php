<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        $this->view('auth/login', [
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    public function login(): void
    {
        if (!Security::validateCsrfToken($this->input('csrf_token'))) {
            $_SESSION['error'] = 'Token de segurança inválido. Tente novamente.';
            $this->redirect('/login');
        }

        $email = $this->input('email');
        $password = $this->input('password');

        if (!Security::isValidEmail($email) || empty($password)) {
            $_SESSION['error'] = 'Preencha email e senha corretamente.';
            $this->redirect('/login');
        }

        $user = $this->userModel->findByEmail($email);

        // Mensagem genérica de propósito: não revela se o email existe ou não
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenciais inválidas.';
            $this->redirect('/login');
        }

        // Previne fixação de sessão
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        $this->redirect('/');
    }

    public function showRegister(): void
    {
        $this->view('auth/register', [
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    public function register(): void
    {
        if (!Security::validateCsrfToken($this->input('csrf_token'))) {
            $_SESSION['error'] = 'Token de segurança inválido. Tente novamente.';
            $this->redirect('/register');
        }

        $name = $this->input('name');
        $email = $this->input('email');
        $password = $this->input('password');
        $passwordConfirm = $this->input('password_confirm');

        if (empty($name) || !Security::isValidEmail($email) || empty($password)) {
            $_SESSION['error'] = 'Preencha todos os campos corretamente.';
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'A senha deve ter no mínimo 8 caracteres.';
            $this->redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            $this->redirect('/register');
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Este email já está cadastrado.';
            $this->redirect('/register');
        }

        $this->userModel->create($name, $email, $password);

        $_SESSION['success'] = 'Cadastro realizado com sucesso. Faça login.';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }
}
