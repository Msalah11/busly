<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\AdminUserListData;
use App\Queries\Builders\UserQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Action for retrieving users list for admin interface.
 */
final class GetUsersListAction
{
    /**
     * Execute the action to get users list.
     *
     * @return LengthAwarePaginator<int, \App\Models\User>
     */
    public function execute(AdminUserListData $data): LengthAwarePaginator
    {
        return (new UserQueryBuilder)
            ->when($data->search, fn ($query): \App\Queries\Builders\UserQueryBuilder => $query->search($data->search))
            ->orderByCreated()
            ->paginate(
                perPage: $data->perPage,
                columns: ['id', 'name', 'email', 'role', 'email_verified_at', 'created_at'],
                pageName: 'page',
                page: $data->page
            );
    }

    /**
     * Get dashboard summary data.
     *
     * @return LengthAwarePaginator<int, \App\Models\User>
     */
    public function getDashboardData(): LengthAwarePaginator
    {
        return (new UserQueryBuilder)
            ->orderByCreated()
            ->paginate(
                perPage: 10,
                columns: ['id', 'name', 'email', 'role', 'created_at']
            );
    }
}
