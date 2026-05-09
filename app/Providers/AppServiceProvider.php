<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('manage-users', fn (User $user) => $user->hasRole('admin'));
        Gate::define('manage-faskes', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang'));
        Gate::define('manage-master-obat', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang'));
        Gate::define('manage-funding-sources', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang'));
        Gate::define('view-rko', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang', 'pimpinan'));
        Gate::define('create-rko', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang'));
        Gate::define('approve-rko', fn (User $user) => $user->hasRole('pimpinan'));
        Gate::define('manage-stock-mutations', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang'));
        Gate::define('view-procurement-realizations', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang', 'pimpinan'));
        Gate::define('view-monitoring', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang', 'pimpinan'));
        Gate::define('view-reports', fn (User $user) => $user->hasAnyRole('admin', 'petugas_gudang', 'pimpinan'));
        Gate::define('view-activity-logs', fn (User $user) => $user->hasAnyRole('admin', 'pimpinan'));

        if ($this->app->runningInConsole()) {
            return;
        }

        if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
