<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\User\GetUserDashboardDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for user dashboard operations.
 */
final class DashboardController extends Controller
{
    /**
     * Display the user dashboard with reservation summary.
     */
    public function index(
        Request $request,
        GetUserDashboardDataAction $dashboardAction
    ): Response {
        $dashboardData = $dashboardAction->execute();

        return Inertia::render('user/dashboard', $dashboardData);
    }
}
