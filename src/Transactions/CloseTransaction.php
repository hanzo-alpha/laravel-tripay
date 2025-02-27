<?php

namespace HanzoAlpha\LaravelTripay\Transactions;

use HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException;
use HanzoAlpha\LaravelTripay\Requests\TripayClient;
use HanzoAlpha\LaravelTripay\Validator\CreateCloseTransactionFormValidation;
use Illuminate\Support\Collection;

class CloseTransaction implements Transaction
{
    protected TripayClient $httpClient;

    protected string $response;

    public function __construct(TripayClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function createTransaction(array $data): \HanzoAlpha\LaravelTripay\Transactions\Transaction
    {
        $validated = CreateCloseTransactionFormValidation::validate($data);

        if (! Signature::validate(
            $this->setSignatureHash($validated),
            $validated['signature']
        )) {
            throw new InvalidSignatureHashException('signature hash tidak valid.');
        }

        $this->response = $this->httpClient->sendRequest('POST', 'transaction/create', $validated);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse(): Collection
    {
        // TODO: Implement getResponse() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getDetailTransaction(string $refNumber): \HanzoAlpha\LaravelTripay\Transactions\Transaction
    {
        // TODO: Implement getDetailTransaction() method.
    }

    /**
     * {@inheritDoc}
     */
    public function setSignatureHash(array $data): string
    {
        // TODO: Implement setSignatureHash() method.
    }
}
