<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\MetricValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Prophecy\Argument;

class MetricValueFactorySpec extends ObjectBehavior
{
    function let(MetricFactory $metricFactory)
    {
        $this->beConstructedWith($metricFactory, MetricValue::class, 'pim_catalog_metric');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MetricValueFactory::class);
    }

    function it_supports_metric_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_metric')->shouldReturn(true);
    }

    function it_creates_an_empty_metric_product_value(
        $metricFactory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->willReturn('Length');
        $attribute->getDefaultMetricUnit()->willReturn('METER');
        $metricFactory->createMetric('Length', 'METER', null)->willReturn($metric);
        $metric->getData()->willReturn(null);
        $metric->getUnit()->willReturn('METER');
        $metric->getFamily()->willReturn('Length');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(MetricValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty('METER');
    }

    function it_creates_a_localizable_and_scopable_empty_metric_product_value(
        $metricFactory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->willReturn('Length');
        $attribute->getDefaultMetricUnit()->willReturn('METER');
        $metricFactory->createMetric('Length', 'METER', null)->willReturn($metric);
        $metric->getData()->willReturn(null);
        $metric->getUnit()->willReturn('METER');
        $metric->getFamily()->willReturn('Length');

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(MetricValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty('METER');
    }

    function it_creates_a_metric_product_value(
        $metricFactory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->willReturn('Length');
        $metricFactory->createMetric('Length', 'GRAM', 42)->willReturn($metric);
        $metric->getData()->willReturn(42);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getFamily()->willReturn('Length');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['amount' => 42, 'unit' => 'GRAM']
        );

        $productValue->shouldReturnAnInstanceOf(MetricValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMetric(['amount' => 42, 'unit' => 'GRAM']);
    }

    function it_creates_a_localizable_and_scopable_metric_product_value(
        $metricFactory,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->willReturn('Length');
        $metricFactory->createMetric('Length', 'GRAM', 42)->willReturn($metric);
        $metric->getData()->willReturn(42);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getFamily()->willReturn('Length');

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['amount' => 42, 'unit' => 'GRAM']
        );

        $productValue->shouldReturnAnInstanceOf(MetricValue::class);
        $productValue->shouldHaveAttribute('metric_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMetric(['amount' => 42, 'unit' => 'GRAM']);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array($metricFactory, AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->shouldNotBeCalled();
        $metricFactory->createMetric(Argument::cetera())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::arrayExpected(
            'metric_attribute',
            MetricValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
    }

    function it_throws_an_exception_if_provided_data_has_no_amount($metricFactory, AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->shouldNotBeCalled();
        $metricFactory->createMetric(Argument::cetera())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'metric_attribute',
            'amount',
            MetricValueFactory::class,
            ['foo' => 42, 'unit' => 'GRAM']
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['foo' => 42, 'unit' => 'GRAM']]);
    }

    function it_throws_an_exception_if_provided_data_has_no_unit($metricFactory, AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('metric_attribute');
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getBackendType()->willReturn('metric');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attribute->getMetricFamily()->shouldNotBeCalled();
        $metricFactory->createMetric(Argument::cetera())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'metric_attribute',
            'unit',
            MetricValueFactory::class,
            ['amount' => 42, 'bar' => 'GRAM']
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['amount' => 42, 'bar' => 'GRAM']]);
    }

    public function getMatchers()
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'    => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'       => function ($subject, $expectedUnit) {
                $metric = $subject->getData();

                return null === $metric->getData() && $expectedUnit === $metric->getUnit();
            },
            'haveMetric'    => function ($subject, $expectedMetric) {
                $metric = $subject->getData();

                return $expectedMetric['amount'] === $metric->getData() &&
                    $expectedMetric['unit'] === $metric->getUnit();
            },
        ];
    }
}
