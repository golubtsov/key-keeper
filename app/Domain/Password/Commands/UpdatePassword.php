<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use Domain\Password\Services\PasswordService;
use LaravelZero\Framework\Commands\Command;

class UpdatePassword extends Command
{
    protected $signature = 'passwords:update {id}';

    protected $description = 'Update password';

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->service->update($this);
    }
}
