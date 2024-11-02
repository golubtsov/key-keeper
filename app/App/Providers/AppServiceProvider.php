<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Password\Commands\AddNewPassword;
use Domain\Password\Commands\DeletePassword;
use Domain\Password\Commands\GetPassword;
use Domain\Password\Commands\MenuCommand;
use Domain\Password\Commands\MyPasswords;
use Domain\Password\Commands\UploadFromFile;
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
            MenuCommand::class,
        ]);
    }

    public function register(): void
    {
    }
}
