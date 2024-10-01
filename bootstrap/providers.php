<?php

return [
    \App\Providers\AppServiceProvider::class,
    config("app.env") === "production" ?:
    \Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
];
