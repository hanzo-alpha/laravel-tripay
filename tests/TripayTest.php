<?php

use HanzoAlpha\LaravelTripay\LaravelTripay;
use HanzoAlpha\LaravelTripay\Requests\TripayClient;
use HanzoAlpha\LaravelTripay\Signature;
use Mockery\MockInterface;

it('can test', function () {
    expect(true)->toBeTrue();
});

it('init configuration', function () {
    LaravelTripay::loadConfig(true, 'api_key', 'private_key', 'merchant_code');

    $this->assertArrayHasKey('tripay_api_production', config('tripay'));
    $this->assertArrayHasKey('tripay_api_key', config('tripay'));
    $this->assertArrayHasKey('tripay_private_key', config('tripay'));

    $this->assertTrue(config('tripay.tripay_api_production'));
    $this->assertEquals('api_key', config('tripay.tripay_api_key'));
    $this->assertEquals('private_key', config('tripay.tripay_private_key'));
    $this->assertEquals('merchant_code', config('tripay.tripay_merchant_code'));
});

/**
 * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidTransactionException
 * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException
 */
it('create open transaction', function () {
    $data = [
        'method' => 'BRIVA',
        'merchant_ref' => 'INV12345',
        'customer_name' => 'Nama Pelanggan',
        'signature' => Signature::generate('BRIVAINV12345'),
    ];

    // fake api production
    config(['tripay.tripay_api_production' => true]);

    /**
     * @var \HanzoAlpha\LaravelTripay\Requests\TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/open_transaction/success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);
    $result = $tripay->createTransaction($data, LaravelTripay::OPEN_TRANSACTION)->getResponse();

    $this->assertTrue($result['success']);
});

/**
 * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidTransactionException
 * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException
 */
it('create close transaction', function () {
    $data = [
        'method' => 'BRIVA',
        'merchant_ref' => 'KODE INVOICE',
        'amount' => 50000,
        'customer_name' => 'Nama Pelanggan',
        'customer_email' => 'emailpelanggan@domain.com',
        'customer_phone' => '081234567890',
        'order_items' => [
            [
                'sku' => 'FB-06',
                'name' => 'Nama Produk 1',
                'price' => 50000,
                'quantity' => 1,
                'product_url' => 'https://tokokamu.com/product/nama-produk-1',
                'image_url' => 'https://tokokamu.com/product/nama-produk-1.jpg',
            ],
        ],
        'return_url' => 'https://domainanda.com/redirect',
        'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
        'signature' => Signature::generate('KODE INVOICE'. 50000),
    ];

    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/close_transaction/success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);
    $result = $tripay->createTransaction($data)->getResponse();

    $this->assertTrue($result['success']);
});

/**
 * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidTransactionException
 */
it('get detail closed transaction', function () {
    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/close_transaction/detail_success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);
    $result = $tripay->getDetailTransaction('T0001000000000000006')->getResponse();

    $this->assertTrue($result['success']);
    $this->assertEquals('T0001000000000000006', $result['data']['reference']);
});

it('get detail open transaction', function () {
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/open_transaction/detail_success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);
    $result = $tripay->getDetailTransaction('T0001OP9376HnpS', LaravelTripay::OPEN_TRANSACTION)
        ->getResponse();
    $this->assertTrue($result['success']);
    $this->assertEquals('T0001OP9376HnpS', $result['data']['uuid']);
});

it('get instruksi pembayaran', function () {
    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/instruksi_pembayaran/success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);

    $result = $tripay->getInstruksiPembayaran('BRIVA');
    // $result = $tripay->getInstruksiPembayaran('BRIVA', '264006510417648');
    // $result = $tripay->getInstruksiPembayaran('BRIVA', '264006510417648', '50000');

    $this->assertTrue($result['success']);
});

it('get channel pembayaran', function () {
    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/channel_pembayaran/success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);

    $result = $tripay->getChannelPembayaran();
    // $result = $tripay->getChannelPembayaran('PERMATAVA');
    $this->assertTrue($result['success']);
    $this->assertCount(14, $result['data']);
});

it('get biaya transaksi', function () {
    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/biaya_transaksi/success.json'));
    });

    $tripay = new LaravelTripay($fakeHttpClient);

    $result = $tripay->getBiayaTransaksi(100000, 'BRIVA');
    // $result = $tripay->getBiayaTransaksi(100000);
    $this->assertTrue($result['success']);
    $this->assertCount(1, $result['data']);
});

it('get daftar transaksi', function () {
    /**
     * @var TripayClient $fakeHttpClient
     */
    $fakeHttpClient = $this->mock(TripayClient::class, function (MockInterface $mock) {
        return $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/mock/daftar_transaksi/success.json'));
    });

    $payload = [
        'page' => 1,
        'per_page' => 3,
        'sort' => 'desc',
    ];

    $tripay = new LaravelTripay($fakeHttpClient);

    $result = $tripay->getDaftarTransaksi($payload);

    $this->assertTrue($result['success']);
});

//it('generate and validate signature hash', function () {
//    $privateKey = config('tripay.tripay_private_key', 'VodDN-Gd63e-J4Vrf-XDggi-tLahm');
//    $merchantCode = config('tripay.tripay_merchant_code', 'T37858');
//    $merchantRef = 'INV55567';
//    $amount = 1500000;
//
//    $signature = hash_hmac('sha256', $merchantCode.$merchantRef.$amount, $privateKey);
//    $generatedSignature = Signature::generate($merchantRef.$amount);
//
//    $this->assertEquals($signature, $generatedSignature);
//    $this->assertTrue(Signature::validate($merchantRef.$amount, $signature));
//});

//it(/**
// * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidTransactionException
// * @throws \HanzoAlpha\LaravelTripay\Exceptions\InvalidSignatureHashException
// */ 'validation parameter exception', function () {
//    $this->expectException(TripayValidationException::class);
//
//    \HanzoAlpha\LaravelTripay\Facades\LaravelTripay::createTransaction([]);
//});
