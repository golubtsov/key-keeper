<?php

declare(strict_types=1);

namespace App\Console;

use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;

class Command extends LaravelZeroCommand
{
    private const YES = 'Yes';

    private const NO = 'No';

    protected function clearConsole(): void
    {
        $answer = $this->choice(
            'Clear console?',
            [self::YES => 1, self::NO => 0],
            1
        );

        if ($answer === self::YES) {
            system('clear');
        }
    }
}
