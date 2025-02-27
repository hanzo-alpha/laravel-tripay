<?php

namespace HanzoAlpha\LaravelTripay\Validator;

interface Validation
{
    /**
     * @param  array  $data
     * @return array
     */
    public static function validate(array $data): array;
}
