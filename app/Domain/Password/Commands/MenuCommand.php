<?php

declare(strict_types=1);

namespace Domain\Password\Commands;

use App\Console\Command;
use Domain\Password\Password;
use Domain\Password\PasswordService;
use Illuminate\Support\Collection;

class MenuCommand extends Command
{
    protected $signature = 'start';

    protected $description = 'Start';

    private array $menu = [];

    private array $columns = ['id', 'login', 'password', 'comment'];

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
            function (Password $password): void {
                $this->menu[$password->id] = $password->resource . ' | ' . $password->login;
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
                'id' => $password['id'],
                'login' => $password['login'],
                'hash' => $password['hash'],
                'comment' => $password['comment'],
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
