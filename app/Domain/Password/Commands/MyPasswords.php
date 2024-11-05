<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use App\Console\Command;
use Domain\Password\PasswordService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MyPasswords extends Command
{

    protected $signature = 'passwords:list';

    protected $description = 'My passwords';
    private array $columns = [
        'id',
        'login',
        'resource',
        'hash',
        'comment',
    ];

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->table(
            $this->columns,
            $this->service->getPasswords($this->options())
        );

        $this->clearConsole();
    }

    protected function configure(): void
    {
        $this->addOption('resource', 'R', InputArgument::OPTIONAL, 'Resource');
        $this->addOption('offset', 'O', InputArgument::OPTIONAL, 'Offset', 0);
        $this->addOption('limit', 'L', InputArgument::OPTIONAL, 'Limit', 10);
        $this->addOption(
            'decrypt',
            'D',
            InputOption::VALUE_NONE,
            'Decrypt password hash'
        );
    }
}
