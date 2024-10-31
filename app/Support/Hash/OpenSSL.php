<?php

declare(strict_types=1);

namespace Support\Hash;

class OpenSSL
{
    private static string $cipher = 'aes-256-cbc';

    public static function encrypt(string $data, string $key): string
    {
        $ivLen = openssl_cipher_iv_length(self::$cipher);

        $iv = openssl_random_pseudo_bytes($ivLen);

        $ciphertextRaw = openssl_encrypt($data, self::$cipher, $key, 0, $iv);

        return base64_encode($iv . $ciphertextRaw);
    }

    public static function decrypt(
        string $cipherText,
        string $key,
    ): false|string {
        $cipherText = base64_decode($cipherText);

        $ivLen = openssl_cipher_iv_length(self::$cipher);

        $iv = substr($cipherText, 0, $ivLen);

        $ciphertext_raw = substr($cipherText, $ivLen);

        return openssl_decrypt($ciphertext_raw, self::$cipher, $key, 0, $iv);
    }
}
