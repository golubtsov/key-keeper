<?php

namespace App\Providers;

use Domain\Password\Commands\AddNewPassword;
use Domain\Password\Commands\DeletePassword;
use Domain\Password\Commands\GetPassword;
use Domain\Password\Commands\UploadFromFile;
use Domain\Password\Commands\MyPasswords;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            MyPasswords::class,
            AddNewPassword::class,
            GetPassword::class,
            DeletePassword::class,
            UploadFromFile::class,
        ]);
    }

    public function register(): void
    {
    }
}
