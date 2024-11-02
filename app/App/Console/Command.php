<?php

declare(strict_types=1);

namespace App\Console;

use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;
use NunoMaduro\LaravelConsoleMenu\Menu;

/**
 * @method Menu menu(string $title = '', array $options = [])
 */
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
