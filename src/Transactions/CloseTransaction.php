<?php

namespace HanzoAlpha\LaravelTripay\Transactions;

use HanzoAlpha\LaravelTripay\Exceptions\InvalidCredentialException;
use HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException;
use HanzoAlpha\LaravelTripay\Requests\TripayClient;
use HanzoAlpha\LaravelTripay\Signature;
use HanzoAlpha\LaravelTripay\Validator\CreateCloseTransactionFormValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class CloseTransaction implements Transaction
{
    /**
     * @var \HanzoAlpha\LaravelTripay\Requests\TripayClient
     */
    protected TripayClient $httpClient;

    /**
     * @var string
     */
    protected string $response;

    /**
     * @param  TripayClient  $httpClient
     */
    public function __construct(TripayClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @inheritDoc
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\TripayValidationException
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidCredentialException
     */
    public function createTransaction(array $data): Transaction
    {
        $validated = CreateCloseTransactionFormValidation::validate($data);

        if (!Signature::validate(
            $this->setSignatureHash($validated),
            $validated['signature']
        )) {
            throw new InvalidSignatureHashException('signature hash tidak valid.');
        }

        $this->response = $this->httpClient->sendRequest('POST', 'transaction/create', $validated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): Collection
    {
        return collect(json_decode($this->response, true));
    }

    /**
     * @inheritDoc
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDetailTransaction(string $refNumber): Transaction
    {
        $validated = Validator::make([
            'reference' => $refNumber
        ], [
            'reference' => 'required|string'
        ])->validate();

        $this->response = $this->httpClient->sendRequest('GET', 'transaction/detail', $validated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSignatureHash(array $data): string
    {
        if (isset($data['merchant_ref']) && isset($data['amount'])) {
            return $data['merchant_ref'].$data['amount'];
        }

        throw new InvalidCredentialException('gagal melakukan hash. data merchant_ref atau amount belum dikonfigurasi');
    }
}
