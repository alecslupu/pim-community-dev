<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Text;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Scopable and localizable attribute text sorter integration tests (simple select)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'        => 'a_localizable_scopable_text',
            'type'        => AttributeTypes::TEXT,
            'localizable' => true,
            'scopable'    => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_scopable_text' => [
                    ['data' => 'cat is beautiful', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'dog is wonderful', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_scopable_text' => [
                    ['data' => 'dog is wonderful', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'cat is beautiful', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_scopable_text',
                Directions::ASCENDING,
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_scopable_text',
                Directions::ASCENDING,
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_scopable_text',
                Directions::DESCENDING,
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_scopable_text',
                Directions::DESCENDING,
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([
            [
                'a_localizable_scopable_text',
                'A_BAD_DIRECTION',
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_scopable_text', Directions::DESCENDING, ['locale' => 'de_DE', 'scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_scopable_text', Directions::ASCENDING, ['locale' => 'de_DE', 'scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }
}
