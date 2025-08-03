<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetDashboardDataAction;
use App\Actions\Admin\User\GetUsersListAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for admin dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(
        Request $request,
        GetUsersListAction $usersAction,
        GetDashboardDataAction $dashboardAction
    ): Response {
        $users = $usersAction->getDashboardData();
        $dashboardData = $dashboardAction->execute();

        return Inertia::render('admin/dashboard', [
            'users' => $users,
            ...$dashboardData,
        ]);
    }
}
