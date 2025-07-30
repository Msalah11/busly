<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Admin\GetUsersListAction;
use App\Http\Requests\Admin\AdminUsersRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(Request $request, GetUsersListAction $action): Response
    {
        $users = $action->getDashboardData();

        return Inertia::render('admin/dashboard', [
            'users' => $users,
        ]);
    }

    /**
     * Display the admin users management page.
     */
    public function users(AdminUsersRequest $request, GetUsersListAction $action): Response
    {
        $listData = $request->toDTO();
        $users = $action->execute($listData);

        return Inertia::render('admin/users', [
            'users' => $users,
            'filters' => [
                'search' => $listData->search,
            ],
        ]);
    }
}
