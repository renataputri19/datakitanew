<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Superadmin user management.
 *
 * Lets a superadmin create accounts, assign exactly one (non-overlapping)
 * role, change roles, reset passwords, and delete accounts — with guards
 * that keep the superadmin from locking everyone out.
 *
 * Access is enforced by the ['auth', 'is_superadmin'] middleware on the
 * route group in routes/web.php.
 */
class UserController extends Controller
{
    /**
     * Shared validation rules for the assignable role.
     */
    private function roleRule(): array
    {
        return ['required', 'string', Rule::in(array_keys(User::roleDefinitions()))];
    }

    /**
     * Display a searchable, role-filterable list of users.
     */
    public function index(Request $request)
    {
        $query = User::with('institution')->orderBy('name');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by canonical role via its backing flag column.
        $roleFilter = $request->input('role');
        $definitions = User::roleDefinitions();
        if ($roleFilter && array_key_exists($roleFilter, $definitions)) {
            $column = $definitions[$roleFilter]['column'];
            if ($column !== null) {
                $query->where($column, true);
            } else {
                // Basic "Pengguna": none of the role flags are set.
                foreach (User::ROLE_FLAG_COLUMNS as $flag) {
                    $query->where($flag, false);
                }
            }
        }

        $users = $query->paginate(12)->withQueryString();
        $roleCounts = User::roleCounts();

        return view('superadmin.users.index', [
            'users'       => $users,
            'roleCounts'  => $roleCounts,
            'definitions' => $definitions,
            'search'      => $search ?? '',
            'roleFilter'  => $roleFilter,
        ]);
    }

    /**
     * Show the create-user form.
     */
    public function create()
    {
        return view('superadmin.users.create', [
            'definitions' => User::roleDefinitions(),
        ]);
    }

    /**
     * Persist a new user with a single assigned role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9])/', 'confirmed'],
            'role'     => $this->roleRule(),
        ], $this->messages());

        $user = new User();
        $user->name     = $validated['name'];
        $user->email    = strtolower($validated['email']);
        $user->password = Hash::make($validated['password']);
        $user->setRole($validated['role']);           // guarantees non-overlapping role
        $user->save();

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', "Pengguna \"{$user->name}\" berhasil ditambahkan sebagai {$user->roleLabel()}.");
    }

    /**
     * Show the edit form for a user.
     */
    public function edit(User $user)
    {
        return view('superadmin.users.edit', [
            'user'        => $user,
            'definitions' => User::roleDefinitions(),
        ]);
    }

    /**
     * Update a user's details and role, with an optional password reset.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role'     => $this->roleRule(),
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9])/', 'confirmed'],
        ], $this->messages());

        // Guard: don't strip the last superadmin of their role.
        if ($user->is_superadmin
            && $validated['role'] !== User::ROLE_SUPERADMIN
            && $this->superadminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Tidak dapat mengubah role: ini adalah satu-satunya Super Admin yang tersisa.',
            ]);
        }

        $user->name  = $validated['name'];
        $user->email = strtolower($validated['email']);
        $user->setRole($validated['role']);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', "Data pengguna \"{$user->name}\" berhasil diperbarui.");
    }

    /**
     * Delete a user, with self- and last-superadmin guards.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()
                ->route('superadmin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->is_superadmin && $this->superadminCount() <= 1) {
            return redirect()
                ->route('superadmin.users.index')
                ->with('error', 'Tidak dapat menghapus satu-satunya Super Admin yang tersisa.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', "Pengguna \"{$name}\" berhasil dihapus.");
    }

    /**
     * Number of active superadmin accounts.
     */
    private function superadminCount(): int
    {
        return User::where('is_superadmin', true)->count();
    }

    /**
     * Indonesian validation messages shared by store/update.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'name.min'          => 'Nama minimal 2 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.regex'    => 'Password harus mengandung huruf dan angka.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role yang dipilih tidak valid.',
        ];
    }
}
