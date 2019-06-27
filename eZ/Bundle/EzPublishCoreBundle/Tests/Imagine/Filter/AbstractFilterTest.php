<?php

/**
 * File containing the AbstractFilterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\AbstractFilter;
use PHPUnit\Framework\TestCase;

class AbstractFilterTest extends TestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\AbstractFilter */
    protected $filter;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->getFilter();
    }

    protected function getFilter()
    {
        return $this->getMockForAbstractClass(AbstractFilter::class);
    }

    public function testGetSetOptions()
    {
        $this->assertSame([], $this->filter->getOptions());
        $options = ['foo' => 'bar', 'some' => ['thing']];
        $this->filter->setOptions($options);
        $this->assertSame($options, $this->filter->getOptions());
    }

    /**
     * @dataProvider getSetOptionNoDefaulValueProvider
     */
    public function testGetSetOptionNoDefaultValue($optionName, $value)
    {
        $this->assertFalse($this->filter->hasOption($optionName));
        $this->assertNull($this->filter->getOption($optionName));
        $this->filter->setOption($optionName, $value);
        $this->assertTrue($this->filter->hasOption($optionName));
        $this->assertSame($value, $this->filter->getOption($optionName));
    }

    public function getSetOptionNoDefaulValueProvider()
    {
        return [
            ['foo', 'bar'],
            ['foo', '123'],
            ['bar', 123],
            ['bar', ['foo', 123]],
            ['bool', true],
            ['obj', new \stdClass()],
        ];
    }

    /**
     * @dataProvider getSetOptionWithDefaulValueProvider
     */
    public function testGetSetOptionWithDefaultValue($optionName, $value, $defaultValue)
    {
        $this->assertFalse($this->filter->hasOption($optionName));
        $this->assertSame($defaultValue, $this->filter->getOption($optionName, $defaultValue));
        $this->filter->setOption($optionName, $value);
        $this->assertTrue($this->filter->hasOption($optionName));
        $this->assertSame($value, $this->filter->getOption($optionName));
    }

    public function getSetOptionWithDefaulValueProvider()
    {
        return [
            ['foo', 'bar', 'default'],
            ['foo', '123', 'default2'],
            ['bar', 123, 0],
            ['bar', ['foo', 123], []],
            ['bool', true, false],
            ['obj', new \stdClass(), new \stdClass()],
        ];
    }
}
