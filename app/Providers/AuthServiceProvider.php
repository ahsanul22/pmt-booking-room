<?php

namespace App\Providers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $areas = [
            'access-general' => [User::ROLE_USER, User::ROLE_ROOM_PIC, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN],
            'access-employee' => [User::ROLE_USER, User::ROLE_ROOM_PIC],
            'access-pic' => [User::ROLE_ROOM_PIC, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN],
            // Admin area: Branch Admin is limited to its branch, Super Admin covers all (DEC-061).
            'access-admin' => [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN],
            // Global master data and the location dropdown.
            'access-super-admin' => [User::ROLE_SUPER_ADMIN],
        ];

        foreach ($areas as $ability => $roles) {
            Gate::define($ability, fn (User $user): bool => $user->is_active && in_array($user->role, $roles, true));
        }

        Gate::define('manage-room', fn (User $user, Room $room): bool => $user->can('access-admin')
            && $user->canAccessBranch($room->branch_id));

        Gate::define('manage-user', function (User $user, User $target): bool {
            if (! $user->can('access-admin')) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return true;
            }
            $branchId = $user->managedBranchId();

            return $branchId !== null && in_array($target->role, $user->assignableRoles(), true)
                && $target->branches()->whereKey($branchId)->exists();
        });
    }
}
