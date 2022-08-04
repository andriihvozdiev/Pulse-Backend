<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('db.connector.mssql', \Illuminate\Database\Connectors\SqlServerConnector::class);
        $this->app->bind('db.connection.mssql', \Miinto\Database\MsSqlConnection::class);
    }
}
