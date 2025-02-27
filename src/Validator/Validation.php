<?php

namespace HanzoAlpha\LaravelTripay\Validator;

interface Validation
{
    public static function validate(array $data): array;
}
