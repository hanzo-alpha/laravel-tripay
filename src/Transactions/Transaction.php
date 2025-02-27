<?php

namespace HanzoAlpha\LaravelTripay\Transactions;

use Illuminate\Support\Collection;

interface Transaction
{
    /**
     * @return $this
     */
    public function createTransaction(array $data): self;

    public function getResponse(): Collection;

    /**
     * @return $this
     */
    public function getDetailTransaction(string $refNumber): self;

    /**
     * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidCredentialException
     */
    public function setSignatureHash(array $data): string;
}
