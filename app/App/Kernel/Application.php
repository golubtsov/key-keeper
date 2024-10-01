<?php

namespace App\Kernel;

use LaravelZero\Framework\Application as ZeroApplication;

class Application extends ZeroApplication
{
    public function path($path = ""): string
    {
        return $this->joinPaths(
            $this->appPath ?: $this->basePath("app/App"),
            $path
        );
    }
}
