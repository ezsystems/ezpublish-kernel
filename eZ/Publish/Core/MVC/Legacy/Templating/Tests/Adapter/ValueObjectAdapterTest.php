<?php
/**
 * File containing the ValueObjectAdapterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Adapter;

use eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter;
use eZ\Publish\API\Repository\Values\ValueObject;

class ValueObjectAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueObject;

    /**
     * @var array
     */
    protected $validProperties;

    /**
     * @var array
     */
    protected $map;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        parent::setUp();
        $block = new \stdClass;
        $block->id = 123456;
        $this->validProperties = array(
            'id'            => 123,
            'identifier'    => 'some_identifier',
            'action'        => 'some_action',
            'blocks'        => array( $block ),
        );
        $this->map = array(
            'id'                => 'id',
            'zone_identifier'   => 'identifier',
            'all_blocks'        => 'blocks',
            'dynamic_prop'      =>
                function ( ValueObject $valueObject )
                {
                    return $valueObject;
                }
        );
        $this->valueObject = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Zone' )
            ->setConstructorArgs(
                array(
                    $this->validProperties
                )
            )
            ->getMockForAbstractClass();
        $this->adapter = $this->getAdapter( $this->valueObject, $this->map );
    }

    /**
     * Returns the adapter to test.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     * @param array $map
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter
     */
    protected function getAdapter( ValueObject $valueObject, array $map )
    {
        return new ValueObjectAdapter( $valueObject, $map );
    }

    /**
     * @dataProvider hasAttributeProvider
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter::hasAttribute
     *
     * @param string$attributeName
     * @param bool $isset
     */
    public function testHasAttribute( $attributeName, $isset )
    {
        $this->assertSame( $isset, $this->adapter->hasAttribute( $attributeName ) );
    }

    public function hasAttributeProvider()
    {
        return array(
            array( 'id', true ),
            array( 'action', false ),
            array( 'zone_identifier', true ),
            array( 'all_blocks', true ),
            array( 'dynamic_prop', true ),
            array( 'non_existent', false ),
        );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter::attributes
     */
    public function testAttributes()
    {
        $this->assertSame( array_keys( $this->map ), $this->adapter->attributes() );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter::attribute
     */
    public function testGetAttribute()
    {
        foreach ( $this->map as $attributeName => $propName )
        {
            switch ( $attributeName )
            {
                case 'dynamic_prop':
                    $this->assertSame( $this->valueObject, $this->adapter->attribute( $attributeName ) );
                    break;
                default:
                    $this->assertSame( $this->validProperties[$propName], $this->adapter->attribute( $attributeName ) );
            }
        }
    }

    public function getAttributeProvider()
    {
        return array(
            array( 'id', $this->validProperties['id'] ),
            array( 'action', null ),
            array( 'zone_identifier', $this->validProperties['identifier'] ),
            array( 'all_blocks', $this->validProperties['blocks'] ),
            array( 'dynamic_prop', $this->valueObject ),
            array( 'non_existent', null ),
        );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Legacy\Templating\Adapter\ValueObjectAdapter::getValueObject
     */
    public function testGetValueObject()
    {
        $this->assertSame( $this->valueObject, $this->adapter->getValueObject() );
    }
}
