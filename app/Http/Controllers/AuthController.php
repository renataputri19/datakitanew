<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Institution;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Enhanced validation with custom messages
        $credentials = $request->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'string'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:255'
            ],
        ], [
            'email.required' => __('validation.required', ['attribute' => 'alamat email']),
            'email.email' => __('validation.email', ['attribute' => 'alamat email']),
            'email.max' => __('validation.max.string', ['attribute' => 'alamat email', 'max' => 255]),
            'password.required' => __('validation.required', ['attribute' => 'kata sandi']),
            'password.min' => __('validation.min.string', ['attribute' => 'kata sandi', 'min' => 6]),
            'password.max' => __('validation.max.string', ['attribute' => 'kata sandi', 'max' => 255]),
        ]);

        // Rate limiting check
        $key = 'login_attempts:' . $request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            return back()->withErrors([
                'email' => __('auth.throttle', ['seconds' => $decayMinutes * 60]),
            ])->onlyInput('email');
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Clear login attempts on successful login
            cache()->forget($key);

            // Unified redirect for all authenticated users
            return redirect()->intended(route('dashboard'));
        }

        // Increment failed login attempts
        $attempts = cache()->get($key, 0) + 1;
        cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function registerForm()
    {
        return view('auth.register');
    }

    /**
     * Check if email is already registered (AJAX endpoint)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json([
                'available' => false,
                'message' => 'Email diperlukan'
            ]);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'available' => false,
                'message' => 'Format email tidak valid'
            ]);
        }

        // Check if email exists in database
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email ini sudah terdaftar. Silakan gunakan email lain atau coba masuk.' : 'Email tersedia'
        ]);
    }

    /**
     * Handle the registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/'
            ],
            'user_type' => [
                'required',
                'string',
                'in:personal,instansi,akademisi'
            ],
            'institution_type' => [
                'required_if:user_type,instansi,akademisi',
                'nullable',
                'string',
                'in:pemerintah,swasta,universitas,sekolah,institut,politeknik,lembaga_penelitian,perusahaan,organisasi,lainnya'
            ],
            'institution_name' => [
                'required_if:user_type,instansi,akademisi',
                'nullable',
                'string',
                'min:2',
                'max:255'
            ],
            'institution_address' => [
                'nullable',
                'string',
                'min:10',
                'max:500'
            ],
            'institution_phone' => [
                'nullable',
                'string',
                'min:10',
                'max:20',
                'regex:/^[\d\-\+\(\)\s]+$/'
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama harus minimal 2 karakter.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, tanda hubung, titik, dan apostrof.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Silakan masukkan alamat email yang valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau coba masuk.',
            'email.regex' => 'Silakan masukkan format email yang valid.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.max' => 'Password tidak boleh lebih dari 255 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung minimal satu huruf besar, satu huruf kecil, dan satu angka.',

            'user_type.required' => 'Silakan pilih jenis pengguna.',
            'user_type.in' => 'Silakan pilih jenis pengguna yang valid.',

            'institution_type.required_if' => 'Jenis institusi wajib diisi untuk pengguna institusi dan akademisi.',
            'institution_type.in' => 'Silakan pilih jenis institusi yang valid.',

            'institution_name.required_if' => 'Nama institusi wajib diisi untuk pengguna institusi dan akademisi.',
            'institution_name.min' => 'Nama institusi harus minimal 2 karakter.',
            'institution_name.max' => 'Nama institusi tidak boleh lebih dari 255 karakter.',

            'institution_address.min' => 'Alamat institusi harus minimal 10 karakter.',
            'institution_address.max' => 'Alamat institusi tidak boleh lebih dari 500 karakter.',

            'institution_phone.min' => 'Nomor telepon harus minimal 10 karakter.',
            'institution_phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
            'institution_phone.regex' => 'Format nomor telepon tidak valid. Gunakan angka, tanda hubung, tanda kurung, atau spasi.',
        ]);

        try {
            // Create or find institution
            $institutionId = null;

            if (!empty($request->user_type) && $request->user_type !== 'personal') {
                // Determine academic type for akademisi users
                $academicType = null;
                $institutionName = trim($request->institution_name);

                if ($request->user_type === 'akademisi') {
                    // Map institution type to academic type
                    $academicTypeMap = [
                        'universitas' => 'university',
                        'sekolah' => 'college',
                        'institut' => 'institute',
                        'politeknik' => 'polytechnic',
                        'lembaga_penelitian' => 'research',
                        'lainnya' => 'other',
                    ];
                    $academicType = $academicTypeMap[$request->institution_type] ?? 'other';
                }

                // Create a new institution
                $institution = Institution::create([
                    'name' => $institutionName,
                    'type' => $request->user_type,
                    'institution_type' => $request->user_type === 'instansi' ? $request->institution_type : null,
                    'academic_type' => $academicType,
                    'address' => $request->user_type === 'instansi' ? trim($request->institution_address) : null,
                    'phone' => $request->user_type === 'instansi' ? trim($request->institution_phone) : null,
                    'website' => null,
                ]);

                $institutionId = $institution->id;
            }

            // Create user with validated data
            $user = User::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'institution_id' => $institutionId,
                'is_bps' => false,
                'is_admin' => false,
                'is_superadmin' => false,
            ]);

            // Log the user in
            Auth::login($user);

            // Unified redirect after successful registration
            return redirect()->route('dashboard')
                ->with('success', 'Pendaftaran berhasil! Selamat datang di DataKita.');

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['registration' => 'Pendaftaran gagal. Silakan coba lagi.']);
        }
    }

    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
