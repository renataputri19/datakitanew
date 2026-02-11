<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\News;
use App\Models\Institution;
use App\Models\Video;
use App\Models\SurveyResponse;

class UserDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get recent news for the dashboard (limit to 4 as per requirements)
        $recentNews = News::orderBy('created_at', 'desc')->take(4)->get();
        // Get recent videos for the dashboard (limit to 2 as per requirements)
        $recentVideos = Video::latest('date')->take(2)->get();
        
        // Sample dashboard stats for basic users
        $stats = [
            'profile_completion' => $this->calculateProfileCompletion($user)
        ];
        
        return view('user-dashboard.index', compact('user', 'stats', 'recentNews', 'recentVideos'));
    }

    /**
     * Display the user profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::user();
        
        return view('user-dashboard.profile', compact('user'));
    }

    /**
     * Display the news page.
     *
     * @return \Illuminate\View\View
     */
    public function news()
    {
        $user = Auth::user();
        
        // Get all news with pagination (4 per page as per requirements)
        $news = News::orderBy('created_at', 'desc')->paginate(4);
        
        return view('user-dashboard.news', compact('user', 'news'));
    }

    /**
     * Display the videos page.
     *
     * @return \Illuminate\View\View
     */
    public function videos()
    {
        $user = Auth::user();
        
        // Get all videos with pagination (2 per page as per requirements)
        $videos = Video::latest('date')->paginate(2);
        
        return view('user-dashboard.videos', compact('user', 'videos'));
    }

    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $user = Auth::user();
        
        return view('user-dashboard.settings', compact('user'));
    }

    /**
     * Display SIBSTR survey results summary for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function sibstrResults()
    {
        $user = Auth::user();

        // Fetch the unified SIBSTR response for the user
        $response = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        // Determine completion
        $isCompleted = (bool) ($response->is_completed ?? false);
        $completedAt = $response->last_saved_at ?? null;

        return view('user-dashboard.sibstr-results', [
            'user' => $user,
            'response' => $response,
            'isCompleted' => $isCompleted,
            'completedAt' => $completedAt,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\.\']++$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'user_type' => ['required', 'in:personal,instansi,akademisi'],
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama harus minimal 2 karakter.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'name.regex' => 'Nama hanya boleh mengandung huruf, spasi, tanda hubung, titik, dan apostrof.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'user_type.required' => 'Tipe pengguna wajib dipilih.',
            'user_type.in' => 'Tipe pengguna tidak valid.',
        ];

        // Add conditional validation for institution fields
        if (in_array($request->user_type, ['instansi', 'akademisi'])) {
            $rules['institution_type'] = ['required', 'in:pemerintah,swasta,universitas,sekolah,institut,politeknik,lembaga_penelitian,perusahaan,organisasi,lainnya'];
            $rules['institution_name'] = ['required', 'string', 'min:2', 'max:255'];
            
            $messages['institution_type.required'] = 'Jenis instansi/akademisi wajib dipilih.';
            $messages['institution_type.in'] = 'Jenis instansi/akademisi tidak valid.';
            $messages['institution_name.required'] = 'Nama instansi/akademisi wajib diisi.';
            $messages['institution_name.min'] = 'Nama instansi/akademisi harus minimal 2 karakter.';
            $messages['institution_name.max'] = 'Nama instansi/akademisi tidak boleh lebih dari 255 karakter.';
            
            // Add address and phone validation for instansi users (both optional)
            if ($request->user_type === 'instansi') {
                $rules['institution_address'] = ['nullable', 'string', 'min:10', 'max:500'];
                $rules['institution_phone'] = ['nullable', 'string', 'min:10', 'max:20', 'regex:/^[\d\-\+\(\)\s]+$/'];

                $messages['institution_address.min'] = 'Alamat institusi harus minimal 10 karakter.';
                $messages['institution_address.max'] = 'Alamat institusi tidak boleh lebih dari 500 karakter.';
                $messages['institution_phone.min'] = 'Nomor telepon harus minimal 10 karakter.';
                $messages['institution_phone.max'] = 'Nomor telepon tidak boleh lebih dari 20 karakter.';
                $messages['institution_phone.regex'] = 'Format nomor telepon tidak valid. Gunakan angka, tanda hubung, tanda kurung, atau spasi.';
            }
        }

        $validatedData = $request->validate($rules, $messages);

        // Sanitize data
        $validatedData['name'] = trim($validatedData['name']);
        $validatedData['email'] = strtolower(trim($validatedData['email']));

        try {
            // Handle institution data
            $institutionId = null;

            if ($validatedData['user_type'] !== 'personal') {
                // Determine academic type for akademisi users
                $academicType = null;
                $institutionName = trim($validatedData['institution_name']);

                if ($validatedData['user_type'] === 'akademisi') {
                    // Map institution type to academic type
                    $academicTypeMap = [
                        'universitas' => 'university',
                        'sekolah' => 'college',
                        'institut' => 'institute',
                        'politeknik' => 'polytechnic',
                        'lembaga_penelitian' => 'research',
                        'lainnya' => 'other',
                    ];
                    $academicType = $academicTypeMap[$validatedData['institution_type']] ?? 'other';
                }

                // Check if user already has an institution
                if ($user->institution_id) {
                    // Update existing institution
                    $institution = Institution::find($user->institution_id);
                    if ($institution) {
                        $institution->update([
                            'name' => $institutionName,
                            'type' => $validatedData['user_type'],
                            'institution_type' => $validatedData['user_type'] === 'instansi' ? $validatedData['institution_type'] : null,
                            'academic_type' => $academicType,
                            'address' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_address'] ?? '') : null,
                            'phone' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_phone'] ?? '') : null,
                        ]);
                        $institutionId = $institution->id;
                    } else {
                        // Create new institution if the old one doesn't exist
                        $institution = Institution::create([
                            'name' => $institutionName,
                            'type' => $validatedData['user_type'],
                            'institution_type' => $validatedData['user_type'] === 'instansi' ? $validatedData['institution_type'] : null,
                            'academic_type' => $academicType,
                            'address' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_address'] ?? '') : null,
                            'phone' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_phone'] ?? '') : null,
                        ]);
                        $institutionId = $institution->id;
                    }
                } else {
                    // Create new institution
                    $institution = Institution::create([
                        'name' => $institutionName,
                        'type' => $validatedData['user_type'],
                        'institution_type' => $validatedData['user_type'] === 'instansi' ? $validatedData['institution_type'] : null,
                        'academic_type' => $academicType,
                        'address' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_address'] ?? '') : null,
                        'phone' => $validatedData['user_type'] === 'instansi' ? trim($validatedData['institution_phone'] ?? '') : null,
                    ]);
                    $institutionId = $institution->id;
                }
            } else {
                // For personal users, remove institution association
                $institutionId = null;
            }

            // Update user with basic info and institution_id
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'institution_id' => $institutionId,
            ]);
            
            return redirect()->route('userdashboard.settings')
                ->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        }
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ];

        $messages = [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru harus minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung minimal satu huruf kecil, satu huruf besar, dan satu angka.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['current_password' => 'Password saat ini tidak benar.']);
        }

        try {
            $user->update([
                'password' => Hash::make($validatedData['password'])
            ]);
            
            return redirect()->route('userdashboard.settings')
                ->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengubah password. Silakan coba lagi.');
        }
    }

    /**
     * Display the apps page with role-based app cards.
     *
     * @return \Illuminate\View\View
     */
    public function apps()
    {
        $user = Auth::user();

        return view('user-dashboard.apps', compact('user'));
    }

    /**
     * Calculate profile completion percentage.
     *
     * @param \App\Models\User $user
     * @return int
     */
    private function calculateProfileCompletion($user)
    {
        $fields = ['name', 'email'];
        $completed = 0;

        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }

        // Add institution fields if applicable
        if ($user->institution && $user->institution->type !== 'personal') {
            $fields[] = 'institution_type_or_academic_type';
            $fields[] = 'institution_name';

            if (!empty($user->institution->institution_type) || !empty($user->institution->academic_type)) {
                $completed++;
            }
            if (!empty($user->institution->name)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}