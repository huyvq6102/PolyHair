<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

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
        // Set default locale to Vietnamese
        App::setLocale('vi');
        
        // MySQL timezone is set via PDO::MYSQL_ATTR_INIT_COMMAND in config/database.php
        // This ensures timezone is set automatically when connection is established
    }
}
