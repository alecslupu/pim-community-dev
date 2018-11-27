<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatFileHeader
{
    /** @var string */
    protected $code;

    /** @var bool */
    protected $isLocalizable;

    /** @var bool */
    protected $isScopable;

    /** @var bool */
    protected $usesUnit;

    /** @var bool */
    protected $usesCurrencies;

    /** @var bool */
    protected $isLocaleSpecific;

    /** @var array */
    protected $specificToLocales;

    public function __construct(
        string $code,
        bool $isScopable,
        bool $isLocalizable,
        bool $usesUnit,
        bool $usesCurrencies,
        bool $isLocaleSpecific,
        ?array $specificToLocales = null
    ) {
        if ($isLocaleSpecific && empty($specificToLocales)) {
            throw new \InvalidArgumentException(
                'A list of locales to which the header is specific to must be provided '.
                'when the header is defined as locale specific'
            );
        }

        if ($usesCurrencies && $usesUnit) {
            throw new \InvalidArgumentException(
                'A header cannot have both currencies and unit.'
            );
        }

        $this->code = $code;
        $this->isScopable = $isScopable;
        $this->isLocalizable = $isLocalizable;
        $this->usesUnit = $usesUnit;
        $this->usesCurrencies = $usesCurrencies;

        $this->isLocaleSpecific = $isLocaleSpecific;
        $this->specificToLocales = $specificToLocales;
    }

    /**
     * Generate headers string contextualized on channel
     */
    public function generateHeaderStrings(
        string $channelCode,
        array $localeCodes,
        array $channelCurrencyCodes,
        array $allCurrencyCodes
    ): array {
        if ($this->isLocaleSpecific && count(array_intersect($localeCodes, $this->specificToLocales)) === 0) {
            return [];
        }

        $prefixes = [];

        if ($this->isLocalizable && $this->isScopable) {
            foreach ($localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s-%s', $this->code, $localeCode, $channelCode);
                }
            }
        } elseif ($this->isLocalizable) {
            foreach ($localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s', $this->code, $localeCode);
                }
            }
        } elseif ($this->isScopable) {
            $prefixes[] = sprintf('%s-%s', $this->code, $channelCode);
        } else {
            $prefixes[] = $this->code;
        }

        $headers = [];

        if ($this->usesCurrencies) {
            foreach ($prefixes as $prefix) {
                if ($this->isScopable) {
                    $currencyCodesToUse = $channelCurrencyCodes;
                } else {
                    $currencyCodesToUse = $allCurrencyCodes;
                }
                foreach ($currencyCodesToUse as $currencyCode) {
                    $headers[] = sprintf('%s-%s', $prefix, $currencyCode);
                }
            }
        } elseif ($this->usesUnit) {
            foreach ($prefixes as $prefix) {
                $headers[] = $prefix;
                $headers[] = sprintf('%s-unit', $prefix);
            }
        } else {
            $headers = $prefixes;
        }

        return $headers;
    }
}
