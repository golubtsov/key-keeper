<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use Domain\Password\PasswordService;
use LaravelZero\Framework\Commands\Command;

class UploadFromFile extends Command
{
    protected $signature = 'passwords:upload {path}';

    protected $description = 'Upload passwords';

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->service->upload($this->argument('path'));
    }
}
