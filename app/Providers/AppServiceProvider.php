<?php

namespace App\Providers;

use Carbon\Carbon;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Carbon diffForHumans auf DEUTSCH
        Carbon::setLocale('de');

        Gate::define('create_todo', fn(User $user) => $user->hasPermissionTo('create_todo'));
        Gate::define('read_todo', fn(User $user) => $user->hasPermissionTo('read_todo'));
        Gate::define('update_todo', fn(User $user) => $user->hasPermissionTo('update_todo'));
        Gate::define('delete_todo', fn(User $user) => $user->hasPermissionTo('delete_todo'));
    }
}
