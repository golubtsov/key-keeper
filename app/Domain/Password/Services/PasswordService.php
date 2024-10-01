<?php

namespace Domain\Password\Services;

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

    public function getPassword(int $id): array
    {
        $this->password = (array) DB::table("passwords")
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
        bool $likeArray = true,
        bool $decrypt = false
    ): Collection|array {
        $this->passwords = DB::table("passwords")
            ->select(
                "passwords.id",
                "passwords.resource",
                "passwords.created_at",
                "passwords.updated_at",
                "passwords.hash"
            )
            ->get();

        !$decrypt ?: $this->decryptPasswords();

        $this->setShortHash()->formatDates();

        return $likeArray ? $this->toArray($this->passwords) : $this->passwords;
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
                "id" => (int) $result[0],
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
        $this->passwords->map(function (stdClass $stdClass) {
            $stdClass->password = OpenSSL::decrypt(
                $stdClass->hash,
                config("openssl.private_key")
            );
            return $stdClass;
        });
        return $this;
    }
}
