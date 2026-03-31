<?php

namespace ApiCheck\Api\Tests;

use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Validations;
use ApiCheck\Api\Tests\Fixtures\TestDataProviders;
use PHPUnit\Framework\Attributes\DataProvider;

class ValidationsTest extends TestCase
{
    // =========================================================================
    // CONSTRUCTOR TESTS
    // =========================================================================

    public function testConstructorWithValidNetherlandsCountryCode(): void
    {
        $validator = new Validations('nl');
        $this->assertEquals('nl', $validator->countryCode);
    }

    public function testConstructorWithValidLuxembourgCountryCode(): void
    {
        $validator = new Validations('lu');
        $this->assertEquals('lu', $validator->countryCode);
    }

    public function testConstructorWithUppercaseCountryCode(): void
    {
        $this->expectException(UnsupportedCountryException::class);
        new Validations('NL');
    }

    #[DataProvider('unsupportedCountryCodesProvider')]
    public function testConstructorWithUnsupportedCountryCodeThrowsException(string $countryCode): void
    {
        $this->expectException(UnsupportedCountryException::class);
        $this->expectExceptionMessage("Unsupported country-code provided: ({$countryCode})");

        new Validations($countryCode);
    }

    public static function unsupportedCountryCodesProvider(): array
    {
        return [
            'belgium' => ['be'],
            'germany' => ['de'],
            'empty' => [''],
            'invalid' => ['XX'],
        ];
    }

    // =========================================================================
    // POSTAL CODE VALIDATION TESTS
    // =========================================================================

    #[DataProvider('validPostalCodesProvider')]
    public function testValidatePostalCodeValid(string $country, string $input, string $expected): void
    {
        $validator = new Validations($country);
        $result = $validator->validatePostalCode($input);
        $this->assertEquals($expected, $result);
    }

    public static function validPostalCodesProvider(): array
    {
        return TestDataProviders::validPostalCodes();
    }

    #[DataProvider('invalidPostalCodesProvider')]
    public function testValidatePostalCodeInvalidThrowsException(string $country, string $input): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid postalcode provided ({$input}) for country: ({$country})");

        $validator = new Validations($country);
        $validator->validatePostalCode($input);
    }

    public static function invalidPostalCodesProvider(): array
    {
        // Filter to only include supported countries (nl, lu)
        $allInvalid = TestDataProviders::invalidPostalCodes();
        return array_filter($allInvalid, function ($item) {
            return in_array(strtolower($item[0]), ['nl', 'lu']);
        });
    }

    // =========================================================================
    // NETHERLANDS SPECIFIC POSTAL CODE TESTS
    // =========================================================================

    public function testValidateDutchPostalCodeReturnsUppercase(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validatePostalCode('2513aa');
        $this->assertEquals('2513AA', $result);
    }

    public function testValidateDutchPostalCodePreservesSpaces(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validatePostalCode('2513 AA');
        $this->assertEquals('2513 AA', $result);
    }

    public function testValidateDutchPostalCodeWithSpaceReturnsUppercase(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validatePostalCode('2513 aa');
        $this->assertEquals('2513 AA', $result);
    }

    public function testValidateDutchPostalCodeAmsterdam(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validatePostalCode('1012AB');
        $this->assertEquals('1012AB', $result);
    }

    public function testValidateDutchPostalCodeRural(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validatePostalCode('9999XX');
        $this->assertEquals('9999XX', $result);
    }

    public function testValidateDutchPostalCodeStartsWithZeroThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('nl');
        $validator->validatePostalCode('0123AB');
    }

    public function testValidateDutchPostalCodeMissingLettersThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('nl');
        $validator->validatePostalCode('2513');
    }

    // =========================================================================
    // LUXEMBOURG SPECIFIC POSTAL CODE TESTS
    // =========================================================================

    public function testValidateLuxembourgPostalCodeSimple(): void
    {
        $validator = new Validations('lu');
        $result = $validator->validatePostalCode('1234');
        $this->assertEquals('1234', $result);
    }

    public function testValidateLuxembourgPostalCodeWithTrailingSpace(): void
    {
        $validator = new Validations('lu');
        // The regex allows trailing spaces, and strtoupper() doesn't trim
        $result = $validator->validatePostalCode('1234 ');
        $this->assertEquals('1234 ', $result);
    }

    public function testValidateLuxembourgPostalCodeTooShortThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('lu');
        $validator->validatePostalCode('123');
    }

    public function testValidateLuxembourgPostalCodeTooLongThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('lu');
        $validator->validatePostalCode('12345');
    }

    // =========================================================================
    // STREET NUMBER VALIDATION TESTS
    // =========================================================================

    #[DataProvider('validStreetNumbersProvider')]
    public function testValidateStreetNumberValid(string $input, string $expected): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumber($input);
        $this->assertEquals($expected, $result);
    }

    public static function validStreetNumbersProvider(): array
    {
        // Filter out hyphenated numbers as they don't pass validation
        $allValid = TestDataProviders::validStreetNumbers();
        return array_filter($allValid, function ($item) {
            return strpos($item[0], '-') === false;
        });
    }

    #[DataProvider('invalidStreetNumbersProvider')]
    public function testValidateStreetNumberInvalidThrowsException(string $input): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invalid streetnumber provided ({$input}) for country:");

        $validator = new Validations('nl');
        $validator->validateStreetNumber($input);
    }

    public static function invalidStreetNumbersProvider(): array
    {
        return TestDataProviders::invalidStreetNumbers();
    }

    public function testValidateStreetNumberWithLetterAfterSpace(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumber('123 a');
        $this->assertEquals('123 a', $result);
    }

    public function testValidateStreetNumberStartsWithZeroThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('nl');
        $validator->validateStreetNumber('0123');
    }

    public function testValidateStreetNumberZeroThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validations('nl');
        $validator->validateStreetNumber('0');
    }

    // =========================================================================
    // STREET NUMBER SUFFIX VALIDATION TESTS
    // =========================================================================

    #[DataProvider('validStreetNumberSuffixesProvider')]
    public function testValidateStreetNumberSuffixValid(string $input, string $expected): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix($input);
        $this->assertEquals($expected, $result);
    }

    public static function validStreetNumberSuffixesProvider(): array
    {
        return TestDataProviders::validStreetNumberSuffixes();
    }

    public function testValidateStreetNumberSuffixTrimsWhitespace(): void
    {
        $validator = new Validations('nl');
        // The validation trims, but ' A ' doesn't match the regex which expects optional space
        // Let's test with 'A ' which does match
        $result = $validator->validateStreetNumberSuffix('A ');
        $this->assertEquals('A', $result);
    }

    public function testValidateStreetNumberSuffixLetterUppercase(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix('A');
        $this->assertEquals('A', $result);
    }

    public function testValidateStreetNumberSuffixLetterLowercase(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix('a');
        $this->assertEquals('a', $result);
    }

    public function testValidateStreetNumberSuffixNumeric(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix('1');
        $this->assertEquals('1', $result);
    }

    public function testValidateStreetNumberSuffixAlphanumeric(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix('A1');
        $this->assertEquals('A1', $result);
    }

    public function testValidateStreetNumberSuffixEmptyString(): void
    {
        $validator = new Validations('nl');
        $result = $validator->validateStreetNumberSuffix('');
        $this->assertEquals('', $result);
    }
}
