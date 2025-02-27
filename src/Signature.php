<?php

namespace HanzoAlpha\LaravelTripay;

class Signature
{
    public static function validate(string $data, string $signatureHash): bool
    {
        $hashed = self::generate($data);

        return $hashed === $signatureHash;
    }

    public static function generate(string $data): string
    {
        $data = config('tripay.tripay_merchant_code').$data;

        return hash_hmac(
            'sha256',
            $data,
            config('tripay.tripay_private_key')
        );
    }
}
