<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\SaveUserRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Branch;
use App\Models\OrganizationalUnit;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        return view('admin.users.index', [
            'users' => User::with(['organizationalUnit', 'branches'])
                // Branch Admin only lists User/PIC accounts of its location (DEC-061).
                ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query
                    ->whereIn('role', $request->user()->assignableRoles())
                    ->whereHas('branches', fn ($branches) => $branches->where('branches.id', $request->user()->managedBranchId() ?? 0)))
                ->when($request->filled('branch_id'), fn ($query) => $query->where(fn ($access) => $access
                    ->where('role', User::ROLE_SUPER_ADMIN)
                    ->orWhereHas('branches', fn ($branches) => $branches->where('branches.id', $request->integer('branch_id')))))
                ->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.create', $this->formOptions($request));
    }

    public function store(SaveUserRequest $request, UserService $service): RedirectResponse
    {
        $user = new User;
        $user = $service->save($user, $request->validated());

        return redirect()->route('admin.users.edit', $user)->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('manage-user', $user);

        return view('admin.users.edit', $this->formOptions($request) + ['userRecord' => $user->load('branches')]);
    }

    public function update(SaveUserRequest $request, User $user, UserService $service): RedirectResponse
    {
        $user = $service->save($user, $request->validated());

        return $this->redirectAfterChange($request, $user, 'Pengguna berhasil diperbarui.');
    }

    public function status(UpdateStatusRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage-user', $user);
        $data = $request->validated();
        $user->is_active = $data['is_active'];
        $user->save();

        return $this->redirectAfterChange($request, $user, $user->is_active ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil dinonaktifkan.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user, UserService $service): RedirectResponse
    {
        $this->authorize('manage-user', $user);
        $service->resetPassword($user, $request->validated('password'));

        return redirect()->route('admin.users.edit', $user)->with('status', 'Password berhasil direset. Gunakan password baru untuk login berikutnya.');
    }

    private function formOptions(Request $request): array
    {
        $actor = $request->user();

        return [
            'units' => OrganizationalUnit::orderBy('name')->get(),
            'branches' => $actor->isSuperAdmin() ? Branch::orderBy('name')->get() : collect(),
            'managedBranch' => $actor->isSuperAdmin() ? null : Branch::find($actor->managedBranchId()),
            'roleOptions' => array_intersect_key(User::roleOptions(), array_flip($actor->assignableRoles())),
        ];
    }

    private function redirectAfterChange(Request $request, User $user, string $message): RedirectResponse
    {
        if ($request->user()->is($user)) {
            if (! $user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
            if (! Gate::forUser($user)->allows('access-admin')) {
                return redirect()->route($user->dashboardRouteName())->with('status', $message);
            }
        }

        return redirect()->route('admin.users.edit', $user)->with('status', $message);
    }
}
