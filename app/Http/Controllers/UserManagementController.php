<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserManagementRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $roleId = trim((string) $request->string('role_id'));
        $status = trim((string) $request->string('status'));

        $users = User::query()
            ->with('role')
            ->withCount(['stockReceipts', 'stockDistributions', 'stockAdjustments'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleId !== '', fn ($query) => $query->where('role_id', $roleId))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        return view('users.index', compact('users', 'roles', 'search', 'roleId', 'status'));
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UserManagementRequest $request): RedirectResponse
    {
        $user = User::create($this->payloadFromRequest($request));

        $this->activityLogService->log(
            (int) $request->user()->id,
            'users',
            'create',
            "Membuat pengguna {$user->name} ({$user->username})",
            $request->ip()
        );

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        $user->load('role');
        $user->loadCount(['stockReceipts', 'stockDistributions', 'stockAdjustments', 'activityLogs']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UserManagementRequest $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id && ! $request->boolean('is_active')) {
            return back()->withErrors([
                'is_active' => 'Akun yang sedang dipakai tidak bisa dinonaktifkan.',
            ])->withInput();
        }

        $user->update($this->payloadFromRequest($request, $user));

        $this->activityLogService->log(
            (int) $request->user()->id,
            'users',
            'update',
            "Memperbarui pengguna {$user->name} ({$user->username})",
            $request->ip()
        );

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id && $user->is_active) {
            return back()->withErrors([
                'toggle_status' => 'Akun yang sedang dipakai tidak bisa dinonaktifkan.',
            ]);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $this->activityLogService->log(
            (int) $request->user()->id,
            'users',
            'toggle_status',
            sprintf(
                '%s pengguna %s (%s)',
                $user->is_active ? 'Mengaktifkan' : 'Menonaktifkan',
                $user->name,
                $user->username
            ),
            $request->ip()
        );

        return redirect()
            ->route('users.index')
            ->with('success', $user->is_active ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(UserManagementRequest $request, ?User $user = null): array
    {
        $payload = [
            'role_id' => $request->integer('role_id'),
            'name' => $request->string('name')->toString(),
            'username' => $request->string('username')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->filled('phone') ? $request->string('phone')->toString() : null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        return $payload;
    }
}
