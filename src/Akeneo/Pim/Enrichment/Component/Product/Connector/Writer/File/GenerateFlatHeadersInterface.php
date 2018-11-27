<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersInterface;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GenerateFlatHeadersInterface
{
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
    ): array;
}
