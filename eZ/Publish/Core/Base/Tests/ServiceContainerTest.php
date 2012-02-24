<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;
use eZ\Publish\Core\Base\ServiceContainer,
    PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ServiceContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testSimpleService()
    {
        $sc = new ServiceContainer(
            array(
                'BService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\B',
                    'public' => true,
                )
            )
        );
        $b = $sc->get('BService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $b );
        self::assertFalse( $b->factoryExecuted );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSimplePrivateService()
    {
        $sc = new ServiceContainer(
            array(
                'BService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\B',
                )
            )
        );
        $sc->get('BService');
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testArgumentsService()
    {
        $sc = new ServiceContainer(
            array(
                'BService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\B',
                 ),
                'CService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\C',
                    'public' => true,
                    'arguments' => array( '@BService' ),
                )
            )
        );
        $c = $sc->get('CService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\C', $c );
        self::assertEquals( '', $c->string );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testComplexService()
    {
        $sc = new ServiceContainer(
            array(
                'AService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\A',
                    'public' => true,
                    'arguments' => array( '@BService', '@CService', '__' ),
                ),
                'BService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\B',
                 ),
                'CService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\C',
                    'arguments' => array( '@BService' ),
                )
            )
        );
        $a = $sc->get('AService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\C', $a->c );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testComplexServiceCustomDependencies()
    {
        $sc = new ServiceContainer(
            array(
                'AService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\A',
                    'public' => true,
                    'arguments' => array( '@BService', '@CService', '__' ),
                ),
                'CService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\C',
                    'factory' => 'factory',
                    'arguments' => array( '@BService', 'B', 'S' ),
                )
            ),
            array( '@BService' => new B )
        );
        $a = $sc->get('AService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\C', $a->c );
        self::assertEquals( '', $a->c->string );// This will change if factory support is re added
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testComplexServiceUsingVariables()
    {
        $sc = new ServiceContainer(
            array(
                'DService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\D',
                    'public' => true,
                    'arguments' => array( '$serviceContainer', '$_SERVER', '$B' ),
                ),
            ),
            array( '$B' => new B )
        );
        $d = $sc->get('DService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\D', $d );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testSimpleServiceUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'EService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\E',
                    'public' => true,
                    'arguments' => array(
                        array(
                            'bool' => true,
                            'int' => 42,
                            'string' => 'Archer',
                            'array' => array( 'ezfile' => 'ezp\\Content\\FieldType\\File', 'something' ),
                        )
                    ),
                ),
            )
        );
        $obj = $sc->get('EService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\E', $obj );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testComplexServiceUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'F' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\F',
                    'public' => true,
                    'arguments' => array(
                        array(
                            'sc' => '$serviceContainer',
                            'b' => '@B',
                            'sub' => array( 'c' => '@C' ),
                        )
                    ),
                ),
                'C' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\C',
                    'arguments' => array( '@B' ),
                )
            ),
            array( '@B' => new B )
        );
        $obj = $sc->get('F');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\F', $obj );
    }
}

class A
{
    public function __construct( B $b, C $c, $string )
    {
        $this->b = $b;
        $this->c = $c;
        $this->string = $string;
    }
}

class B
{
    public $factoryExecuted = false;
    public function __construct(){}
    public static function factory()
    {
        $b = new self();
        $b->factoryExecuted = true;
        return $b;
    }
}

class C
{
    public $string = '';
    public function __construct( B $b ){}
    public static function factory( B $b, $string, $string2 )
    {
        $c = new self( $b );
        $c->string = $string.$string2;
        return $c;
    }
}

class D
{
    public function __construct( ServiceContainer $sc, array $server, B $b ){}
}

class E
{
    public function __construct( array $config )
    {
        if ( $config['bool'] !== true )
            throw new \Exception( "Bool was not 'true' value" );
        if ( $config['string'] !== 'Archer' )
            throw new \Exception( "String was not 'Archer' value" );
        if ( $config['int'] !== 42 )
            throw new \Exception( "Int was not '42' value" );
        if ( $config['array'] !== array( 'ezfile' => 'ezp\\Content\\FieldType\\File', 'something' ) )
            throw new \Exception( "Array was not expected value" );
    }
}

class F
{
    public function __construct( array $config )
    {
        if ( !$config['sc'] instanceof ServiceContainer )
            throw new \Exception( "sc was not instance of 'ServiceContainer'" );
        if ( !$config['b'] instanceof B )
            throw new \Exception( "b was not instance of 'B'" );
        if ( !$config['sub']['c'] instanceof C )
            throw new \Exception( "sub.c was not instance of 'C'" );
    }
}
