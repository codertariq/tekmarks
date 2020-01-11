<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Activity::saving(function (Activity $activity) {
            $activity->properties = $activity->properties->put('ip', getClientIp());
            $activity->properties = $activity->properties->put('user_agent', \Request::header('User-Agent'));
        });
    }
}
