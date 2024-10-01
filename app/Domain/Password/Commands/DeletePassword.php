<?php

namespace Domain\Password\Commands;

use Domain\Password\Services\PasswordService;
use LaravelZero\Framework\Commands\Command;

class DeletePassword extends Command
{
    protected $signature = "passwords:delete {id}";

    protected $description = " - Delete password";

    public function handle(): void
    {
        /** @var PasswordService $service */
        $service = app(PasswordService::class);

        $deleted = $service->delete($this->argument("id"));

        if ($deleted) {
            $this->info("Deleted!");
        } elseif ($deleted === false) {
            $this->info("Not found");
        } else {
            $this->info("Something went wrong(");
        }
    }
}
