<?php

/**
 * File containing the FilterConfigurationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration;
use PHPUnit_Framework_TestCase;

class FilterConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock('\eZ\Publish\Core\MVC\ConfigResolverInterface');
        $this->filterConfiguration = new FilterConfiguration();
        $this->filterConfiguration->setConfigResolver($this->configResolver);
    }

    public function testGetOnlyImagineFilters()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);

        $this->configResolver
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue(array()));

        $this->assertSame($fooConfig, $this->filterConfiguration->get('foo'));
        $this->assertSame($barConfig, $this->filterConfiguration->get('bar'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetNoEzVariationInvalidImagineFilter()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue(array()));

        $this->filterConfiguration->get('foobar');
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidVariationException
     */
    public function testGetWithEzVariationInvalidFilters()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);

        $variations = array(
            'some_variation' => array(),
        );
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->filterConfiguration->get('some_variation');
    }

    public function testGetEzVariationNoReference()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);

        $filters = array('some_filter' => array());
        $variations = array(
            'some_variation' => array('filters' => $filters),
        );
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->assertSame(
            array(
                'cache' => 'ezpublish',
                'data_loader' => 'ezpublish',
                'reference' => null,
                'filters' => $filters,
                'post_processors' => array(),
            ),
            $this->filterConfiguration->get('some_variation')
        );
    }

    public function testGetEzVariationWithReference()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);

        $filters = array('some_filter' => array());
        $reference = 'another_variation';
        $variations = array(
            'some_variation' => array('filters' => $filters, 'reference' => $reference),
        );
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->assertSame(
            array(
                'cache' => 'ezpublish',
                'data_loader' => 'ezpublish',
                'reference' => $reference,
                'filters' => $filters,
                'post_processors' => array(),
            ),
            $this->filterConfiguration->get('some_variation')
        );
    }

    public function testGetEzVariationImagineFilters()
    {
        $filters = array('some_filter' => array());
        $imagineConfig = array('filters' => $filters);
        $this->filterConfiguration->set('some_variation', $imagineConfig);

        $reference = 'another_variation';
        $variations = array(
            'some_variation' => array('reference' => $reference),
        );
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->assertSame(
            array(
                'cache' => 'ezpublish',
                'data_loader' => 'ezpublish',
                'reference' => $reference,
                'filters' => $filters,
                'post_processors' => array(),
            ),
            $this->filterConfiguration->get('some_variation')
        );
    }

    public function testGetEzVariationImagineOptions()
    {
        $imagineConfig = array(
            'foo_option' => 'foo',
            'bar_option' => 'bar',
        );
        $this->filterConfiguration->set('some_variation', $imagineConfig);

        $filters = array('some_filter' => array());
        $reference = 'another_variation';
        $variations = array(
            'some_variation' => array('reference' => $reference, 'filters' => $filters),
        );
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->assertSame(
            array(
                'cache' => 'ezpublish',
                'data_loader' => 'ezpublish',
                'reference' => $reference,
                'filters' => $filters,
                'post_processors' => array(),
                'foo_option' => 'foo',
                'bar_option' => 'bar',
            ),
            $this->filterConfiguration->get('some_variation')
        );
    }

    public function testAll()
    {
        $fooConfig = array('fooconfig');
        $barConfig = array('barconfig');
        $this->filterConfiguration->set('foo', $fooConfig);
        $this->filterConfiguration->set('bar', $barConfig);
        $this->filterConfiguration->set('some_variation', array());

        $filters = array('some_filter' => array());
        $reference = 'another_variation';
        $eZVariationConfig = array('filters' => $filters, 'reference' => $reference);
        $variations = array('some_variation' => $eZVariationConfig);
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('image_variations')
            ->will($this->returnValue($variations));

        $this->assertEquals(
            array(
                'foo' => $fooConfig,
                'bar' => $barConfig,
                'some_variation' => $eZVariationConfig,
            ),
            $this->filterConfiguration->all()
        );
    }
}
