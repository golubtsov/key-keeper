<?php

namespace App\Command\Traits;

trait NeedClearConsoleTrait
{
    private const YES = 'Yes';

    private const NO = 'No';

    private function clearConsole(): void
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
