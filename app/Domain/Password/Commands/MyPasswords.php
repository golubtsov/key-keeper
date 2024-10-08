<?php

namespace Domain\Password\Commands;

use LaravelZero\Framework\Commands\Command;
use Domain\Password\Services\PasswordService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MyPasswords extends Command
{
    protected $signature = "passwords:list";

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

    protected function configure(): void
    {
        $this->addOption("resource", "R", InputArgument::OPTIONAL, "Resource");
        $this->addOption("offset", "O", InputArgument::OPTIONAL, "Offset", 0);
        $this->addOption("limit", "L", InputArgument::OPTIONAL, "Limit", 10);
        $this->addOption(
            "decrypt",
            "D",
            InputOption::VALUE_NONE,
            "Decrypt password hash"
        );
    }

    public function handle(): void
    {
        $this->table(
            $this->columns,
            $this->service->getPasswords($this->options())
        );
    }
}
