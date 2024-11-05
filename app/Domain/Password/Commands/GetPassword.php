<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use App\Console\Command;
use Domain\Password\Services\PasswordService;

class GetPassword extends Command
{
    protected $signature = 'passwords:get {id}';

    protected $description = 'Get password';

    private array $columns = ['id', 'resource', 'password'];

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $data = $this->service->getPassword((int) $this->argument('id'));

        if (count($data) === 0) {
            $this->info('Not found');
        } else {
            $this->table($this->columns, [$data]);
        }

        $this->clearConsole();
    }
}
