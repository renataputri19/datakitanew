<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Show the user profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        return view('bps.profile');
    }

    /**
     * Display a list of all registered users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::with('institution')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        return view('bps.users.index', compact('users'));
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password'              => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9])/', 'confirmed'],
            'new_password_confirmation' => 'required|string',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password baru minimal 8 karakter.',
            'new_password.regex'     => 'Password baru harus mengandung huruf dan angka.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => "Password untuk akun \"{$user->name}\" berhasil diperbarui.",
        ]);
    }
}
