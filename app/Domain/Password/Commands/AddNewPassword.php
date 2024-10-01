<?php

namespace Domain\Password\Commands;

use Exception;
use Support\Hash\OpenSSL;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class AddNewPassword extends Command
{
    protected $signature = "passwords:new_password";

    protected $description = " - Add new password";

    private static string $key;

    public function __construct()
    {
        parent::__construct();
        static::$key = config("openssl.private_key");
    }

    public function handle(): void
    {
        $resource = $this->ask("Enter resource");

        $login = $this->ask("Enter login");

        $password = $this->secret("Enter password");

        $hash = OpenSSL::encrypt($password, static::$key);

        try {
            DB::beginTransaction();

            DB::table("passwords")->insert([
                "hash" => $hash,
                "login" => $login,
                "resource" => $resource,
                "created_at" => now(),
                "updated_at" => now(),
            ]);

            DB::commit();

            $this->info("Password saved!");
        } catch (Exception $exception) {
            DB::rollBack();
            $this->error("Something went wrong!");
            $this->error("Message: " . $exception->getMessage());
            $this->error("Line: " . $exception->getLine());
        }
    }
}
