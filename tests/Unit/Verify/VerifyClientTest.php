<?php

namespace ApiCheck\Api\Tests\Verify;

use ApiCheck\Api\Tests\TestCase;
use ApiCheck\Api\Verify\VerifyClient;
use ApiCheck\Api\Tests\Fixtures\TestDataProviders;
use PHPUnit\Framework\Attributes\DataProvider;

class VerifyClientTest extends TestCase
{
    // =========================================================================
    // EMAIL VERIFICATION TESTS
    // =========================================================================

    public function testVerifyEmailWithValidEmail(): void
    {
        $responseData = [
            'disposable_email' => false,
            'greylisted' => false,
            'status' => 'valid',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('test@example.com');

        $this->assertIsObject($result);
        $this->assertEquals('valid', $result->status);
        $this->assertFalse($result->disposable_email);
        $this->assertFalse($result->greylisted);
    }

    public function testVerifyEmailWithInvalidEmail(): void
    {
        $responseData = [
            'disposable_email' => false,
            'greylisted' => false,
            'status' => 'invalid',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('invalid@example.com');

        $this->assertEquals('invalid', $result->status);
    }

    public function testVerifyEmailWithDisposableEmail(): void
    {
        $responseData = [
            'disposable_email' => true,
            'greylisted' => false,
            'status' => 'valid',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('test@temp-mail.org');

        $this->assertTrue($result->disposable_email);
    }

    public function testVerifyEmailWithGreylistedEmail(): void
    {
        $responseData = [
            'disposable_email' => false,
            'greylisted' => true,
            'status' => 'unknown',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('test@example.com');

        $this->assertTrue($result->greylisted);
        $this->assertEquals('unknown', $result->status);
    }

    public function testVerifyEmailHandlesUnknownStatus(): void
    {
        $responseData = [
            'disposable_email' => false,
            'greylisted' => false,
            'status' => 'unknown',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('test@example.com');

        $this->assertEquals('unknown', $result->status);
    }

    public function testVerifyEmailReturnsExpectedProperties(): void
    {
        $responseData = [
            'disposable_email' => false,
            'greylisted' => false,
            'status' => 'valid',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyEmail('test@example.com');

        $this->assertObjectHasProperties($result, [
            'disposable_email',
            'greylisted',
            'status',
        ]);
    }

    // =========================================================================
    // PHONE VERIFICATION TESTS
    // =========================================================================

    public function testVerifyPhoneWithValidNumber(): void
    {
        $responseData = [
            'valid' => true,
            'country_code' => 'NL',
            'area_code' => '6',
            'international_formatted' => '+31 6 12345678',
            'number_type' => 'mobile',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+31612345678');

        $this->assertIsObject($result);
        $this->assertTrue($result->valid);
        $this->assertEquals('NL', $result->country_code);
        $this->assertEquals('6', $result->area_code);
    }

    public function testVerifyPhoneWithInvalidNumber(): void
    {
        $responseData = [
            'valid' => false,
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+3112345678');

        $this->assertFalse($result->valid);
    }

    public function testVerifyPhoneReturnsCountryCode(): void
    {
        $responseData = [
            'valid' => true,
            'country_code' => 'NL',
            'area_code' => '6',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+31612345678');

        $this->assertEquals('NL', $result->country_code);
    }

    public function testVerifyPhoneReturnsAreaCode(): void
    {
        $responseData = [
            'valid' => true,
            'country_code' => 'NL',
            'area_code' => '10',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+31101234567');

        $this->assertEquals('10', $result->area_code);
    }

    public function testVerifyPhoneReturnsInternationalFormatted(): void
    {
        $responseData = [
            'valid' => true,
            'international_formatted' => '+31 6 12345678',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+31612345678');

        $this->assertEquals('+31 6 12345678', $result->international_formatted);
    }

    public function testVerifyPhoneReturnsNumberType(): void
    {
        $responseData = [
            'valid' => true,
            'number_type' => 'mobile',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone('+31612345678');

        $this->assertEquals('mobile', $result->number_type);
    }

    #[DataProvider('validPhoneNumbersProvider')]
    public function testVerifyPhoneWithDifferentFormats(string $phoneNumber): void
    {
        $responseData = ['valid' => true];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $verifyClient = new VerifyClient($client);

        $result = $verifyClient->verifyPhone($phoneNumber);

        $this->assertTrue($result->valid);
    }

    public static function validPhoneNumbersProvider(): array
    {
        $numbers = TestDataProviders::validPhoneNumbers();
        return array_map(function ($number) {
            return [$number];
        }, $numbers);
    }
}
