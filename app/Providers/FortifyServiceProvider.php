<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind custom LoginResponse
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Register view for Fortify
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        // Login lockout — multi-dimensional so it stops both attack shapes:
        //   1. burst brute-force on one account from one IP,
        //   2. one account brute-forced from many IPs (botnet),
        //   3. one IP spraying one password across many accounts.
        // A failed attempt that trips ANY limit is blocked. Successful logins
        // don't count, so a legitimate user is rarely affected.
        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input(Fortify::username())));
            $ip = $request->ip();

            return [
                // Burst guard: 5 tries / 15 min for this exact email+IP.
                Limit::perMinutes(15, 5)->by('login:'.$email.'|'.$ip),
                // Account guard: 10 tries / hour against this account from anywhere.
                Limit::perHour(10)->by('login_email:'.$email),
                // Spray guard: 30 failed tries / hour from this IP across all accounts.
                Limit::perHour(30)->by('login_ip:'.$ip),
            ];
        });

        // Registration: 5 accounts per hour per IP.
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
