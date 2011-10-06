<?php
/**
 * File contains: ezp\Base\Tests\ServiceContainerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\ServiceContainer;

/**
 * Test case for ServiceContainer class
 *
 */
class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testSimpleService()
    {
        $sc = new ServiceContainer();
        $b = $sc->get(
            'BService',
            array(
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B'
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\B', $b );
        self::assertFalse( $b->factoryExecuted );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testSimpleServiceFactory()
    {
        $sc = new ServiceContainer();
        $b = $sc->get(
            'BService',
            array(
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B',
                    'factory' => 'factory',
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\B', $b );
        self::assertTrue( $b->factoryExecuted );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testArgumentsService()
    {
        $sc = new ServiceContainer();
        $c = $sc->get(
            'CService',
            array(
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B',
                 ),
                'CService' => array(
                    'class' => 'ezp\\Base\\Tests\\C',
                    'arguments' => array( '@BService' ),
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\C', $c );
        self::assertEquals( '', $c->string );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testArgumentsServiceFactory()
    {
        $sc = new ServiceContainer();
        $c = $sc->get(
            'CService',
            array(
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B',
                 ),
                'CService' => array(
                    'class' => 'ezp\\Base\\Tests\\C',
                    'factory' => 'factory',
                    'arguments' => array( '@BService', 'B', 'S' ),
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\C', $c );
        self::assertEquals( 'BS', $c->string );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testComplexService()
    {
        $sc = new ServiceContainer();
        $a = $sc->get(
            'AService',
            array(
                'AService' => array(
                    'class' => 'ezp\\Base\\Tests\\A',
                    'arguments' => array( '@BService', '@CService', '__' ),
                ),
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B',
                 ),
                'CService' => array(
                    'class' => 'ezp\\Base\\Tests\\C',
                    'arguments' => array( '@BService' ),
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\C', $a->c );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testComplexServiceFactory()
    {
        $sc = new ServiceContainer();
        $a = $sc->get(
            'AService',
            array(
                'AService' => array(
                    'class' => 'ezp\\Base\\Tests\\A',
                    'arguments' => array( '@BService', '@CService', '__' ),
                ),
                'BService' => array(
                    'class' => 'ezp\\Base\\Tests\\B',
                    'factory' => 'factory',
                 ),
                'CService' => array(
                    'class' => 'ezp\\Base\\Tests\\C',
                    'factory' => 'factory',
                    'arguments' => array( '@BService', 'B', 'S' ),
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\C', $a->c );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testComplexServiceCustomDependencies()
    {
        $sc = new ServiceContainer( array( '@BService' => new B ) );
        $a = $sc->get(
            'AService',
            array(
                'AService' => array(
                    'class' => 'ezp\\Base\\Tests\\A',
                    'arguments' => array( '@BService', '@CService', '__' ),
                ),
                'CService' => array(
                    'class' => 'ezp\\Base\\Tests\\C',
                    'factory' => 'factory',
                    'arguments' => array( '@BService', 'B', 'S' ),
                )
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\C', $a->c );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testComplexServiceUsingVariables()
    {
        $sc = new ServiceContainer( array( '$B' => new B ) );
        $d = $sc->get(
            'DService',
            array(
                'DService' => array(
                    'class' => 'ezp\\Base\\Tests\\D',
                    'arguments' => array( '$serviceContainer', '$_SERVER', '$B' ),
                ),
            )
        );
        self::assertInstanceOf( 'ezp\\Base\\Tests\\D', $d );
    }

    /**
     * @covers \ezp\Base\ServiceContainer::get
     */
    public function testSimpleServiceUsingHash()
    {
        $sc = new ServiceContainer();
        $d = $sc->get(
            'EService',
            array(
                'EService' => array(
                    'class' => 'ezp\\Base\\Tests\\E',
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
        self::assertInstanceOf( 'ezp\\Base\\Tests\\E', $d );
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