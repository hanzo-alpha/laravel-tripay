<?php

namespace HanzoAlpha\LaravelTripay\Transactions;

use HanzoAlpha\LaravelTripay\Exceptions\InvalidCredentialException;
use HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException;
use HanzoAlpha\LaravelTripay\Requests\TripayClient;
use HanzoAlpha\LaravelTripay\Signature;
use HanzoAlpha\LaravelTripay\Validator\CreateCloseTransactionFormValidation;
use HanzoAlpha\LaravelTripay\Validator\CreateOpenTransactionFormValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class OpenTransaction implements Transaction
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
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException
     */
    public function createTransaction(array $data): Transaction
    {
        if (!config('tripay.tripay_api_production')) {
            throw new InvalidCredentialException('tidak dapat menggunakan api ini dalam mode sandbox.');
        }

        $validated = CreateOpenTransactionFormValidation::validate($data);

        if (!Signature::validate(
            $this->setSignatureHash($validated),
            $validated['signature']
        )) {
            throw new InvalidSignatureHashException('siganture hash salah. silahkan coba lagi.');
        }

        $this->response = $this->httpClient->sendRequest('POST', 'open-payment/create', $validated);

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
     */
    public function getDetailTransaction(string $refNumber): Transaction
    {
        $validated = Validator::make(['uuid' => $refNumber], [
            'uuid' => 'required|string'
        ])->validate();

        $endpoint = 'open-payment/'.$validated['uuid'].'/detail';

        $this->response = $this->httpClient->sendRequest('GET', $endpoint, []);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSignatureHash(array $data): string
    {
        if (isset($data['method']) && isset($data['merchant_ref'])) {
            return $data['method'].$data['merchant_ref'];
        }

        throw new InvalidCredentialException('gagal melakukan hash. data method atau merchant_ref belum dikonfigurasi.');
    }

    /**
     * @param  string  $uuid
     * @param  array  $data
     * @return Transaction
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidCredentialException|\GuzzleHttp\Exception\GuzzleException
     */
    public function getDaftarPembayaran(string $uuid, array $data = []): Transaction
    {
        if (!config('tripay.tripay_api_production')) {
            throw new InvalidCredentialException('tidak dapat menggunakan api ini dalam mode sandbox.');
        }

        $validatedData = Validator::make($data, [
            'reference' => 'bail|nullable|string',
            'merchant_ref' => 'bail|nullable|string',
            'start_date' => 'bail|nullable|string|date_format:Y-m-d H:i:s',
            'end_date' => 'bail|nullable|string|date_format:Y-m-d H:i:s',
            'per_page' => 'bail|nullable|int'
        ])->validate();

        $endpoint = 'open-payment/'.$uuid.'/transactions';

        $this->response = $this->httpClient->sendRequest('GET', $endpoint, $validatedData);

        return $this;
    }
}
