<?php

namespace Domain\Password\Services;

use Domain\Password\Models\Password;
use Exception;
use Illuminate\Database\Query\Builder;
use LaravelZero\Framework\Commands\Command;
use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Support\Collection\Traits\ConvertCollectionStdClassToArray;
use Support\Hash\OpenSSL;

class PasswordService
{
    use ConvertCollectionStdClassToArray;

    /**
     * @var Collection<stdClass>|array
     */
    private Collection|array $passwords;

    private array $password;

    private ?string $resource;

    private int $offset;

    private int $limit;

    private bool $isDecrypt;

    private static string $key;

    public function __construct()
    {
        static::$key = config("openssl.private_key");
    }

    public function initOptions(array $options): void
    {
        $this->resource = $options["resource"] ?? null;
        $this->offset = $options["offset"];
        $this->limit = $options["limit"];
        $this->isDecrypt = $options["decrypt"] ?? false;
    }

    public function getPassword(int $id): array
    {
        $this->isDecrypt = true;

        $this->password = (array)DB::table("passwords")
            ->select("passwords.id", "passwords.resource", "passwords.hash")
            ->find($id);

        if (count(array_keys($this->password)) === 0) {
            return [];
        }

        $this->decryptPassword();

        return $this->password;
    }

    /**
     * @return Collection<stdClass>|array
     */
    public function getPasswords(
        array $options,
        bool  $likeArray = true
    ): Collection|array
    {
        $this->initOptions($options);

        $this->passwords = DB::table("passwords")
            ->select(
                "passwords.id",
                "passwords.resource",
                "passwords.created_at",
                "passwords.updated_at",
                "passwords.hash"
            )
            ->when(is_string($this->resource), function (Builder $builder) {
                $builder->where(
                    "passwords.resource",
                    "like",
                    "%" . $this->resource . "%"
                );
            })
            ->when(isset($this->limit), function (Builder $builder) {
                $builder->limit($this->limit);
            })
            ->when(isset($this->offset), function (Builder $builder) {
                $builder->offset($this->offset);
            })
            ->get();

        $this->decryptPasswords();

        $this->setShortHash()->formatDates();

        return $likeArray ? $this->toArray($this->passwords) : $this->passwords;
    }

    public function delete(int $id): ?bool
    {
        $password = DB::table("passwords")
            ->where("passwords.id", $id)
            ->first();
        return is_null($password) ? false : $password->delete;
    }

    /**
     * @return Collection<stdClass>
     */
    private function groupByResource(): Collection
    {
        /** @var Collection<stdClass> $passwords */
        $passwords = DB::table("passwords")
            ->select("passwords.id", "passwords.resource")
            ->selectRaw(
                'GROUP_CONCAT(CONCAT(passwords.id, "|", passwords.created_at)) as passwords'
            )
            ->groupBy("passwords.resource")
            ->get()
            ->collect();

        $passwords->map(function (stdClass $stdClass) {
            $stdClass->passwords = $this->parsePasswordDataFromString(
                $stdClass->passwords
            );
        });

        return $passwords;
    }

    private function parsePasswordDataFromString(string $data): array
    {
        return array_map(function (string $item) {
            $result = explode("|", $item);
            return [
                "id" => (int)$result[0],
                "created_at" => Carbon::parse($result[1])->timestamp,
            ];
        }, explode(",", $data));
    }

    private function formatDates(): static
    {
        $this->passwords->map(function (stdClass $stdClass) {
            $stdClass->updated_at = Carbon::parse(
                $stdClass->updated_at
            )->format("d.m.Y");
            $stdClass->created_at = Carbon::parse(
                $stdClass->created_at
            )->format("d.m.Y");
            return $stdClass;
        });
        return $this;
    }

    private function setShortHash(): static
    {
        $this->passwords->map(function (stdClass $stdClass) {
            $stdClass->hash = Str::substr($stdClass->hash, 0, 8) . "...";
            return $stdClass;
        });
        return $this;
    }

    private function decryptPassword(): static
    {
        $this->password["password"] = OpenSSL::decrypt(
            $this->password["hash"],
            config("openssl.private_key")
        );
        unset($this->password["hash"]);
        return $this;
    }

    private function decryptPasswords(): static
    {
        if ($this->isDecrypt) {
            $this->passwords->map(function (stdClass $stdClass) {
                $stdClass->password = OpenSSL::decrypt(
                    $stdClass->hash,
                    config("openssl.private_key")
                );
                return $stdClass;
            });
        }
        return $this;
    }

    public function create(Command $command): void
    {
        $resource = $command->ask("Enter resource");

        $login = $command->ask("Enter login");

        $password = $command->secret("Enter password");

        $hash = OpenSSL::encrypt($password, static::$key);

        try {
            DB::beginTransaction();

            $this->insertInDb($hash, $login, $resource);

            DB::commit();

            $command->info("Password saved!");
        } catch (Exception $exception) {
            DB::rollBack();
            $command->error("Something went wrong!");
            $command->error("Message: " . $exception->getMessage());
            $command->error("Line: " . $exception->getLine());
        }
    }

    public function insertInDb(string $hash, string $login, string $resource): void
    {
        DB::table("passwords")->insert([
            "hash" => $hash,
            "login" => $login,
            "resource" => $resource,
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }

    public function getHash(string $string): string
    {
        return OpenSSL::encrypt($string, static::$key);
    }

    public function upload(string $path): void
    {
        $row = 1;
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row !== 1) {
                    $resource = $data[0];
                    $login = $data[2];
                    $password = $data[3];
                    $hash = $this->getHash($password);
                    $this->insertInDb($hash, $login, $resource);
                }
                $row++;
            }
            fclose($handle);
        }
    }
}
