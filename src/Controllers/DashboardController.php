<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->view('dashboard/index', [
            'totalUsers'     => $this->userModel->count(),
            'todayUsers'     => $this->userModel->countCreatedToday(),
            'monthUsers'     => $this->userModel->countCreatedThisMonth(),
            'latestUsers'    => $this->userModel->latest(5),
        ]);
    }
}
