<?php

namespace Domain\Password\Commands;

use LaravelZero\Framework\Commands\Command;
use Domain\Password\Services\PasswordService;

class MyPasswords extends Command
{
    protected $signature = "passwords:my_passwords {--hash : decrypt password hash}";

    protected $description = " - My passwords";

    private PasswordService $service;

    private array $columns = [
        "id",
        "resource",
        "created_at",
        "updated_at",
        "hash",
        "password",
    ];

    public function __construct()
    {
        parent::__construct();
        $this->service = new PasswordService();
    }

    public function handle(): void
    {
        $this->table(
            $this->columns,
            $this->service->getPasswords(decrypt: $this->option("hash"))
        );
    }
}
