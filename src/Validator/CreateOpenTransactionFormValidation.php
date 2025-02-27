<?php

namespace HanzoAlpha\LaravelTripay\Validator;

use HanzoAlpha\LaravelTripay\Exceptions\TripayValidationException;
use HanzoAlpha\LaravelTripay\Validator\Validation;
use Illuminate\Support\Facades\Validator;

class CreateOpenTransactionFormValidation implements Validation
{

    /**
     * @inheritDoc
     * @return array
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\TripayValidationException
     */
    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'method' => 'bail|required|string',
            'merchant_ref' => 'bail|nullable|string',
            'customer_name' => 'bail|nullable|string',
            'signature' => 'bail|required|string'
        ]);

        if ($validator->fails()) {
            throw new TripayValidationException($validator);
        }

        return $validator->validate();
    }
}
