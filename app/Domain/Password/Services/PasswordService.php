<?php

declare(strict_types=1);

namespace Domain\Password\Services;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use stdClass;
use Support\Collection\ConvertCollectionStdClassesToArray;
use Support\Hash\OpenSSL;

class PasswordService
{
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

    public function __construct(
        private readonly ConvertCollectionStdClassesToArray $convert
    ) {
        self::$key = config('openssl.private_key');
    }

    public function initOptions(array $options): void
    {
        $this->resource = $options['resource'] ?? null;
        $this->offset = (int) $options['offset'];
        $this->limit = (int) $options['limit'];
        $this->isDecrypt = (bool) $options['decrypt'];
    }

    public function getPassword(int $id): array
    {
        $this->isDecrypt = true;

        $this->password = (array) DB::table('passwords')
            ->select(
                'passwords.id',
                'passwords.resource',
                'passwords.login',
                'passwords.hash'
            )
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
        bool $likeArray = true
    ): Collection|array {
        $this->initOptions($options);

        $this->passwords = $this->getPasswordsCollection();

        $this->decryptPasswords();

        $this->setShortHash();

        return $likeArray ? $this->convert->toArray($this->passwords) : $this->passwords;
    }

    /**
     * @return Collection<stdClass>
     */
    public function getPasswordsByResourceOrLogin(string $search): Collection
    {
        return DB::table('passwords')
            ->selectRaw('passwords.id, passwords.resource, passwords.login')
            ->where('resource', 'like', '%' . $search . '%')
            ->orWhere('login', 'like', '%' . $search . '%')
            ->get();
    }

    public function delete(int $id): ?bool
    {
        $password = DB::table('passwords')
            ->where('passwords.id', $id)
            ->first();
        return is_null($password) ? false : $password->delete;
    }

    public function create(Command $command): void
    {
        $resource = $command->ask('Enter resource');

        $login = $command->ask('Enter login');

        $password = $command->secret('Enter password');

        $hash = OpenSSL::encrypt($password, self::$key);

        try {
            DB::beginTransaction();

            $this->insertInDb($hash, $login, $resource);

            DB::commit();

            $command->info('Password saved!');
        } catch (Exception $exception) {
            DB::rollBack();
            $command->error('Something went wrong!');
            $command->error('Message: ' . $exception->getMessage());
            $command->error('Line: ' . $exception->getLine());
        }
    }

    public function insertInDb(string $hash, string $login, string $resource): void
    {
        DB::table('passwords')->insert([
            'hash' => $hash,
            'login' => $login,
            'resource' => $resource,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getHash(string $string): string
    {
        return OpenSSL::encrypt($string, self::$key);
    }

    public function upload(string $path): void
    {
        DB::beginTransaction();

        try {
            $row = 1;
            if (($handle = fopen($path, 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
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

            DB::commit();
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getLine() . PHP_EOL;
            DB::rollBack();
        }
    }

    private function getPasswordsCollection(): Collection
    {
        return DB::table('passwords')
            ->select(
                'passwords.id',
                'passwords.login',
                'passwords.resource',
                'passwords.hash'
            )
            ->when(
                is_string($this->resource),
                function (Builder $builder): void {
                    $builder->where(
                        'passwords.resource',
                        'like',
                        '%' . $this->resource . '%'
                    );
                }
            )
            ->when(isset($this->limit), function (Builder $builder): void {
                $builder->limit($this->limit);
            })
            ->when(isset($this->offset), function (Builder $builder): void {
                $builder->offset($this->offset);
            })
            ->get();
    }

    private function setShortHash(): void
    {
        $this->passwords->map(function (stdClass $stdClass) {
            $stdClass->hash = Str::substr($stdClass->hash, 0, 8) . '...';
            return $stdClass;
        });
    }

    private function decryptPassword(): void
    {
        $this->password['password'] = OpenSSL::decrypt(
            $this->password['hash'],
            config('openssl.private_key')
        );
        unset($this->password['hash']);
    }

    private function decryptPasswords(): void
    {
        if ($this->isDecrypt) {
            $this->passwords->map(function (stdClass $stdClass) {
                $stdClass->password = OpenSSL::decrypt(
                    $stdClass->hash,
                    config('openssl.private_key')
                );
                return $stdClass;
            });
        }
    }
}
