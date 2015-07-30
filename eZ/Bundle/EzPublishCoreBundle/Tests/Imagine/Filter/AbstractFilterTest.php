<?php

/**
 * File containing the AbstractFilterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter;

use PHPUnit_Framework_TestCase;

class AbstractFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\AbstractFilter
     */
    protected $filter;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->getFilter();
    }

    protected function getFilter()
    {
        return $this->getMockForAbstractClass('\eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\AbstractFilter');
    }

    public function testGetSetOptions()
    {
        $this->assertSame(array(), $this->filter->getOptions());
        $options = array('foo' => 'bar', 'some' => array('thing'));
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
        return array(
            array('foo', 'bar'),
            array('foo', '123'),
            array('bar', 123),
            array('bar', array('foo', 123)),
            array('bool', true),
            array('obj', new \stdClass()),
        );
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
        return array(
            array('foo', 'bar', 'default'),
            array('foo', '123', 'default2'),
            array('bar', 123, 0),
            array('bar', array('foo', 123), array()),
            array('bool', true, false),
            array('obj', new \stdClass(), new \stdClass()),
        );
    }
}
