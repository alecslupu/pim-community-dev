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
    protected $isScopable;

    /** @var string */
    protected $channelCode;

    /** @var bool */
    protected $isLocalizable;

    /** @var array */
    protected $localeCodes;

    /** @var bool */
    protected $isMedia;

    /** @var bool */
    protected $usesUnit;

    /** @var bool */
    protected $usesCurrencies;

    /** @var array */
    protected $channelCurrencyCodes;

    /** @var array */
    protected $allCurrencyCodes;

    /** @var bool */
    protected $isLocaleSpecific;

    /** @var array */
    protected $specificToLocales;

    public function __construct(
        string $code,
        bool $isScopable,
        ?string $channelCode,
        bool $isLocalizable,
        ?array $localeCodes,
        bool $isMedia,
        bool $usesUnit,
        bool $usesCurrencies,
        ?array $channelCurrencyCodes,
        ?array $allCurrencyCodes,
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
        $this->channelCode = $channelCode;

        $this->isLocalizable = $isLocalizable;
        $this->localeCodes = $localeCodes;

        $this->isMedia = $isMedia;
        $this->usesUnit = $usesUnit;

        $this->usesCurrencies = $usesCurrencies;
        $this->channelCurrencyCodes = $channelCurrencyCodes;
        $this->allCurrencyCodes = $allCurrencyCodes;

        $this->isLocaleSpecific = $isLocaleSpecific;
        $this->specificToLocales = $specificToLocales;
    }

    /**
     * Indicate whether the header is associated to a media information
     */
    public function isMedia(): bool
    {
        return $this->isMedia;
    }

    /**
     * Generate headers string contextualized on channel
     */
    public function generateHeaderStrings(): array
    {
        if ($this->isLocaleSpecific && count(array_intersect($this->localeCodes, $this->specificToLocales)) === 0) {
            return [];
        }

        $prefixes = [];

        if ($this->isLocalizable && $this->isScopable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s-%s', $this->code, $localeCode, $this->channelCode);
                }
            }
        } elseif ($this->isLocalizable) {
            foreach ($this->localeCodes as $localeCode) {
                if (!$this->isLocaleSpecific ||
                    ($this->isLocaleSpecific && in_array($localeCode, $this->specificToLocales))) {
                    $prefixes[] = sprintf('%s-%s', $this->code, $localeCode);
                }
            }
        } elseif ($this->isScopable) {
            $prefixes[] = sprintf('%s-%s', $this->code, $this->channelCode);
        } else {
            $prefixes[] = $this->code;
        }

        $headers = [];

        if ($this->usesCurrencies) {
            foreach ($prefixes as $prefix) {
                if ($this->isScopable) {
                    $currencyCodesToUse = $this->channelCurrencyCodes;
                } else {
                    $currencyCodesToUse = $this->allCurrencyCodes;
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
