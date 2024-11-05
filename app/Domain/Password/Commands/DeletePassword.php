<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use Domain\Password\PasswordService;
use LaravelZero\Framework\Commands\Command;

class DeletePassword extends Command
{
    protected $signature = 'passwords:delete {id}';

    protected $description = 'Delete password';

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $deleted = $this->service->delete((int) $this->argument('id'));

        if ($deleted) {
            $this->info('Deleted!');
        } elseif ($deleted === false) {
            $this->info('Not found');
        } else {
            $this->info('Something went wrong(');
        }
    }
}
