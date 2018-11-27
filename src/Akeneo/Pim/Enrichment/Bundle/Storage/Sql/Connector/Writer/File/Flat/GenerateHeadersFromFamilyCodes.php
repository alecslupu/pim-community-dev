<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateHeadersFromFamilyCodes implements GenerateFlatHeadersInterface
{
    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Generate all possible headers from the provided attribute codes if not empty
     * or from family codes
     */
    public function __invoke(
        ?array $familyCodes,
        ?array $attributeCodes,
        string $channelCode,
        array $localeCodes,
        bool $withMedia
    ): array {
        $channelCurrencyCodes = $this->getChannelCurrencyCodes($channelCode);
        $activatedCurrencyCodes = $this->getActivatedCurrencyCodes();
        $mediaAttributeTypes = [
            AttributeTypes::IMAGE,
            AttributeTypes::FILE
        ];

        $headers = [];
        $rawAttributesData = [];

        if (null !== $attributeCodes) {
            $rawAttributesData = $this->getAttributeDataFromAttributeCodes($attributeCodes);
        } elseif (null !== $familyCodes) {
            $rawAttributesData = $this->getAttributeDataFromFamilyCodes($familyCodes);
        }

        foreach ($rawAttributesData as $rawAttributeData) {
            if ($withMedia || !in_array($rawAttributeData['attribute_type'], $mediaAttributeTypes)) {
                $header = new FlatFileHeader(
                    $rawAttributeData["code"],
                    ("1" === $rawAttributeData["is_scopable"]),
                    ("1" === $rawAttributeData["is_localizable"]),
                    (AttributeTypes::METRIC === $rawAttributeData['attribute_type']),
                    (AttributeTypes::PRICE_COLLECTION === $rawAttributeData['attribute_type']),
                    (null !== $rawAttributeData['specific_to_locales']),
                    null !== $rawAttributeData['specific_to_locales'] ? explode(',', $rawAttributeData['specific_to_locales']) : []
                );

                $headers = array_merge(
                    $headers,
                    $header->generateHeaderStrings(
                        $channelCode,
                        $localeCodes,
                        $channelCurrencyCodes,
                        $activatedCurrencyCodes
                    )
                );
            }
        }

        return $headers;
    }

    /**
     * Retrieve atribute data from attributes matching the provided attributes codes
     */
    protected function getAttributeDataFromAttributeCodes(array $attributeCodes): array
    {
        $attributesDataSql = <<<SQL
            SELECT a.code,
                   a.is_scopable,
                   a.is_localizable,
                   a.attribute_type,
                   GROUP_CONCAT(l.code) AS specific_to_locales
            FROM pim_catalog_attribute
              JOIN pim_catalog_attribute a
              LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = a.id
              LEFT JOIN pim_catalog_locale l on l.id = al.locale_id
            WHERE a.code IN (:attributeCodes)
            GROUP BY a.id;
SQL;

        return $this->connection->executeQuery(
            $attributesDataSql,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();
    }

    /**
     * Retrieve atribute data from attributes belonging to families matching
     * the provided families codes
     */
    protected function getAttributeDataFromFamilyCodes(array $familyCodes): array
    {
        $attributesDataSql = <<<SQL
            SELECT a.code,
                   a.is_scopable,
                   a.is_localizable,
                   a.attribute_type,
                   GROUP_CONCAT(l.code) AS specific_to_locales
            FROM pim_catalog_family f
              JOIN pim_catalog_family_attribute fa ON fa.family_id = f.id
              JOIN pim_catalog_attribute a ON a.id = fa.attribute_id
              LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = a.id
              LEFT JOIN pim_catalog_locale l on l.id = al.locale_id
            WHERE f.code IN (:familyCodes)
            GROUP BY a.id;
SQL;

        return $this->connection->executeQuery(
            $attributesDataSql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();
    }

    /**
     * Retrieve all activated currencies codes
     */
    protected function getActivatedCurrencyCodes(): array
    {
        $currencyCodesSql = <<<SQL
            SELECT currency.code
            FROM pim_catalog_currency currency
            WHERE currency.is_activated = 1
SQL;

        return $this->connection->executeQuery($currencyCodesSql)->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Retrieve currencies codes associated to the provided channel code
     */
    protected function getChannelCurrencyCodes(string $channelCode): array
    {
        $channelCurrencyCodesSql = <<<SQL
            SELECT currency.code
            FROM pim_catalog_channel channel
              JOIN pim_catalog_channel_currency cc ON cc.channel_id = channel.id
              JOIN pim_catalog_currency currency ON currency.id = cc.currency_id
            WHERE channel.code = :channelCode
SQL;

        return $this->connection->executeQuery(
            $channelCurrencyCodesSql,
            ['channelCode' => $channelCode]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
