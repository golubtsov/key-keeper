<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use Domain\Password\PasswordService;
use LaravelZero\Framework\Commands\Command;

class AddNewPassword extends Command
{
    protected $signature = 'passwords:new';

    protected $description = 'Add new password';

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->service->create($this);
    }
}
