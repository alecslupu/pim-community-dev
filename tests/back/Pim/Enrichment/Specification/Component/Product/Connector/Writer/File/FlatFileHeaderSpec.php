<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

use  Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlatFileHeaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {

        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->shouldHaveType(FlatFileHeader::class);
    }

    function it_must_be_throw_an_exception_when_missing_specific_to_locales_list()
    {
        $this->shouldThrow('\InvalidArgumentException')->during(
            '__construct',
            [
                $code = 'my_code',
                $isScopable = false,
                $isLocalizable = false,
                $usesUnit = false,
                $usesCurrencies = false,
                $isLocaleSpecific = true
            ]
        );
    }

    function it_must_be_throw_an_exception_when_using_unit_and_currencies()
    {
        $this->shouldThrow('\InvalidArgumentException')->during(
            '__construct',
            [
                $code = 'my_code',
                $isScopable = false,
                $isLocalizable = false,
                $usesUnit = true,
                $usesCurrencies = true,
                $isLocaleSpecific = false
            ]
        );
    }

    function it_generates_an_empty_header_string_for_non_supported_locales()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = true,
            $specificToLocales = ['de_DE']
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([]);
    }

    function it_generates_a_header_string_if_locales_supported()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn(['my_code']);
    }

    function it_generates_header_strings_for_supported_locales_when_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US', 'de_DE', 'fr_BE']
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR', 'fr_BE'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US',
            'my_code-fr_BE'
        ]);
    }

    function it_generates_a_header_string_if_locales_supported_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = true,
            $usesCurrencies = false,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code',
            'my_code-unit'
        ]);
    }

    function it_generates_a_header_string_if_locales_supported_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-USD',
            'my_code-EUR',
            'my_code-GBP'
        ]);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn(['my_code']);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = true,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code',
            'my_code-unit'
        ]);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-USD',
            'my_code-EUR',
            'my_code-GBP'
        ]);
    }

    function it_generates_a_header_string_for_scopable_non_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn(['my_code-ecommerce']);
    }

    function it_generates_a_header_string_for_scopable_non_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = false,
            $usesUnit = true,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-ecommerce',
            'my_code-ecommerce-unit'
        ]);
    }

    function it_generates_a_header_string_for_scopable_non_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-ecommerce-USD',
            'my_code-ecommerce-EUR'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US',
            'my_code-fr_FR'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = true,
            $usesUnit = true,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US',
            'my_code-en_US-unit',
            'my_code-fr_FR',
            'my_code-fr_FR-unit'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $isLocalizable = true,
            $usesUnit = false,
            $usesCurrencies = true,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US-USD',
            'my_code-en_US-EUR',
            'my_code-en_US-GBP',
            'my_code-fr_FR-USD',
            'my_code-fr_FR-EUR',
            'my_code-fr_FR-GBP'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US-ecommerce',
            'my_code-fr_FR-ecommerce'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = true,
            $usesUnit = true,
            $usesCurrencies = false,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US-ecommerce',
            'my_code-en_US-ecommerce-unit',
            'my_code-fr_FR-ecommerce',
            'my_code-fr_FR-ecommerce-unit'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $isLocalizable = true,
            $usesUnit = false,
            $usesCurrencies = true,
            $isLocaleSpecific = false
        );

        $this->generateHeaderStrings(
            'ecommerce',
            ['en_US', 'fr_FR'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'GBP']
        )->shouldReturn([
            'my_code-en_US-ecommerce-USD',
            'my_code-en_US-ecommerce-EUR',
            'my_code-fr_FR-ecommerce-USD',
            'my_code-fr_FR-ecommerce-EUR'
        ]);
    }
}
