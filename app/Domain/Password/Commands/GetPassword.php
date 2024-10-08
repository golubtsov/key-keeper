<?php

namespace Domain\Password\Commands;

use Domain\Password\Services\PasswordService;
use LaravelZero\Framework\Commands\Command;

class GetPassword extends Command
{
    protected $signature = "passwords:get_password {id}";

    protected $description = " - Get password";

    private PasswordService $service;

    private array $columns = ["id", "resource", "password"];

    public function __construct()
    {
        parent::__construct();
        $this->service = new PasswordService();
    }

    public function handle(): void
    {
        $data = $this->service->getPassword($this->argument("id"));

        if (count($data) === 0) {
            $this->info("Not found");
        } else {
            $this->table($this->columns, [$data]);
        }
    }
}
