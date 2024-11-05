<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use App\Console\Command;
use Domain\Password\PasswordService;
use Domain\Password\PasswordStdClass;
use Illuminate\Support\Collection;
use stdClass;

class MenuCommand extends Command
{
    protected $signature = 'start';

    protected $description = 'Start';

    private array $menu = [];

    private array $columns = ['login', 'password'];

    public function __construct(private readonly PasswordService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $answer = null;

        while ($answer === null) {
            $answer = $this->ask('Enter resource name or login');
        }

        /** @var Collection<PasswordStdClass> $passwords */
        $passwords = $this->service->getPasswordsByResourceOrLogin($answer);

        $this->createMenu($passwords);

        $passwordId = $this->getSelectedPassword();

        $this->table(
            $this->columns,
            $this->getRowsForTable(
                $this->service->getPassword($passwordId)
            )
        );

        $this->clearConsole();
    }

    private function createMenu(Collection $passwords): void
    {
        $passwords->map(
            function (stdClass $passwordsStdClass): void {
                /** @var PasswordStdClass $passwordsStdClass */
                $this->menu[$passwordsStdClass->id] = $passwordsStdClass->resource . ' | ' . $passwordsStdClass->login;
            }
        );
    }

    /**
     * @return array<array>
     */
    private function getRowsForTable(array $password): array
    {
        return [
            [
                'login' => $password['login'],
                'password' => $password['password'],
            ],
        ];
    }

    private function getSelectedPassword(): ?int
    {
        $passwordId = $this->menu('Keys', $this->menu)
            ->setBackgroundColour('black')
            ->open();

        if ($passwordId === null) {
            $this->info('Not found');
            exit(0);
        }

        return $passwordId;
    }
}
