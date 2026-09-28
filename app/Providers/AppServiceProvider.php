<?php

namespace App\Providers;

use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Connection;
use Illuminate\Database\SqlServerConnection;
use App\Database\Query\Grammars\SqlServerGrammar as CustomSqlServerGrammar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            $conn = new SqlServerConnection($connection, $database, $prefix, $config);
            $conn->setQueryGrammar(new CustomSqlServerGrammar());
            return $conn;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*ADD THIS LINES*/
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);

        Paginator::useBootstrap();

        try {
            if (\DB::connection()->getDriverName() === 'sqlsrv') {
                \DB::connection()->setQueryGrammar(new CustomSqlServerGrammar());
            }
        } catch (\Throwable $e) {
            // DB connection not initialized during early boot
        }
    }
}

