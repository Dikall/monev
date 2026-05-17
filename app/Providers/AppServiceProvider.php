<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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
        Paginator::useBootstrapFive();
        Carbon::setLocale('id');

            View::composer('*', function ($view) {
                $user = Auth::user();
                $view->with('currentUser', $user);
                $view->with('appSettings', Cache::rememberForever('app_settings', function () {
                    return \App\Models\AppSetting::all()->pluck('value', 'key');
                }));
                
                if ($user) {
                    $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                        ->whereNull('read_at')
                        ->count();
                    $view->with('unreadNotificationsCount', $unreadCount);
                } else {
                    $view->with('unreadNotificationsCount', 0);
                }
            });
    }
}
