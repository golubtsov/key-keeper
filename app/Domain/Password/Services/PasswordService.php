<?php

declare(strict_types=1);

namespace Domain\Password\Services;

use Domain\Password\Commands\UpdatePassword;
use Domain\Password\Models\Password;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use stdClass;
use Support\Collection\ConvertCollectionStdClassesToArray;
use Support\Hash\OpenSSL;

final class PasswordService
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
    )
    {
        self::$key = config('openssl.private_key');
    }

    public function initOptions(array $options): void
    {
        $this->resource = $options['resource'] ?? null;
        $this->offset = (int)$options['offset'];
        $this->limit = (int)$options['limit'];
        $this->isDecrypt = (bool)$options['decrypt'];
    }

    public function getPassword(int $id): array
    {
        $this->isDecrypt = true;

        $this->password = (array)DB::table('passwords')
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

    public function update(UpdatePassword $command): void
    {
        /** @var Password $password */
        $password = Password::query()->find(
            (int)$command->argument('id')
        );

        if (is_null($password)) {
            $command->info('Not found');
            return;
        }

        $password = $this->newValuesForPassword($command, $password);

        if ($password->isDirty()) {
            try {
                DB::beginTransaction();
                $password->save();
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                $command->error($exception->getMessage());
                return;
            }
        }

        $command->info('Password updated!');
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

        $this->passwords = $this->getPasswordsCollection();

        $this->decryptPasswords();

        $this->shortHashView();

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

        $comment = $command->ask('Add comment');

        $hash = OpenSSL::encrypt($password, self::$key);

        try {
            DB::beginTransaction();

            Password::query()->create([
                'login' => $login,
                'resource' => $resource,
                'hash' => $hash,
                'comment' => $comment,
            ]);

            DB::commit();

            $command->info('Password saved!');
        } catch (Exception $exception) {
            DB::rollBack();
            $command->error('Something went wrong!');

            if (config('app.env') === 'development') {
                $command->error($exception->getMessage());
            }
        }
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
            $handle = fopen($path, 'r');
            if ($handle !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($row !== 1) {
                        $resource = $data[0];
                        $login = $data[2];
                        $password = $data[3];
                        $hash = $this->getHash($password);
                        Password::query()->create([
                            'login' => $login,
                            'resource' => $resource,
                            'hash' => $hash,
                        ]);
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

    private function newValuesForPassword(
        UpdatePassword $command,
        Password       $password
    ): Password
    {
        $resource = $command->ask('Enter new resource');

        $login = $command->ask('Enter new login');

        $newPassword = $command->secret('Enter new password');

        $comment = $command->ask('Enter comment to this password');

        if ($resource) {
            $password->resource = $resource;
        }

        if ($login) {
            $password->login = $login;
        }

        if ($newPassword) {
            $password->hash = OpenSSL::encrypt(
                $newPassword,
                self::$key
            );
        }

        $password->comment = $comment;

        return $password;
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

    private function shortHashView(): void
    {
        $this->passwords->map(static function (stdClass $stdClass) {
            $stdClass->hash = Str::substr($stdClass->hash, 0, 8) . '...';
            return $stdClass;
        });
    }

    private function decryptPassword(): void
    {
        $this->password['password'] = OpenSSL::decrypt(
            $this->password['hash'],
            self::$key
        );
        unset($this->password['hash']);
    }

    private function decryptPasswords(): void
    {
        if ($this->isDecrypt) {
            $this->passwords->map(static function (stdClass $stdClass) {
                $stdClass->password = OpenSSL::decrypt(
                    $stdClass->hash,
                    self::$key
                );
                return $stdClass;
            });
        }
    }
}
