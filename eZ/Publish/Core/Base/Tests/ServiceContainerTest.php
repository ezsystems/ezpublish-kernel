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
    PHPUnit_Framework_TestCase,
    Closure;

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
                )
            )
        );
        $b = $sc->get('BService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $b );
        self::assertFalse( $b->factoryExecuted );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
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
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testComplexService()
    {
        $sc = new ServiceContainer(
            array(
                'AService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\A',
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
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testComplexServiceCustomDependencies()
    {
        $sc = new ServiceContainer(
            array(
                'AService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\A',
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
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testComplexServiceUsingVariables()
    {
        $sc = new ServiceContainer(
            array(
                'DService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\D',
                    'arguments' => array( '$_SERVER', '$B' ),
                ),
            ),
            array( '$B' => new B )
        );
        $d = $sc->get('DService');
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\D', $d );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testSimpleServiceUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'EService' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\E',
                    'arguments' => array(
                        array(
                            'bool' => true,
                            'int' => 42,
                            'string' => 'Archer',
                            'array' => array( 'ezfile' => 'eZ\\Publish\\Core\\Repository\\FieldType\\File', 'something' ),
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
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testComplexServiceUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'F' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\F',
                    'arguments' => array(
                        array(
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

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testComplexLazyLoadedServiceUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'G' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\G',
                    'arguments' => array(
                            'lazyHServiceCall' => '%H-parent::timesTwo',
                            'hIntValue' => 42,
                            'lazyHService' => '%H-parent',
                    ),
                ),
                'H-parent' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\H',
                    'shared' => false,
                    'arguments' => array(),
                ),
                '-parent' => array(
                    'arguments' => array( 'test' => 33 ),
                ),
            )
        );
        $obj = $sc->get('G');
        self::assertEquals( 42 * 2, $obj->hIntValue );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::expandExtendedServices
     */
    public function testComplexExtendedServicesUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestCheck',
                    'arguments' => array(
                            'extendedTests' => '@-ExtendedTest',
                    ),
                ),
                'ExtendedTest1-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                ),
                'ExtendedTest2-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                ),
                'ExtendedTest3-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                ),
                '-ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H'),
                ),
            ),
            array( '$H' => new H )

        );
        $obj = $sc->get('ExtendedTestCheck');
        self::assertEquals( 3, $obj->count );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::expandExtendedServices
     */
    public function testComplexLazyExtendedServicesUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestLacyCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestLacyCheck',
                    'arguments' => array(
                            'extendedTests' => '%-ExtendedTest',
                    ),
                ),
                'ExtendedTest1-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                ),
                'ExtendedTest2-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                ),
                'ExtendedTest3-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                ),
                '-ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H'),
                ),
            ),
            array( '$H' => new H )

        );
        $obj = $sc->get('ExtendedTestLacyCheck');
        self::assertEquals( 3, $obj->count );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::expandExtendedServices
     */
    public function testComplexLazyExtendedCallbackUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestLacyCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestLacyCheck',
                    'arguments' => array(
                            'extendedTests' => '%-ExtendedTest::setTest',
                            'test' => 'newValue',
                    ),
                ),
                'ExtendedTest1-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                ),
                'ExtendedTest2-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                ),
                'ExtendedTest3-ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                ),
                '-ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H' ),
                ),
            ),
            array( '$H' => new H )

        );
        $obj = $sc->get('ExtendedTestLacyCheck');
        self::assertEquals( 3, $obj->count );
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
    public function __construct( array $server, B $b ){}
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
        if ( $config['array'] !== array( 'ezfile' => 'eZ\\Publish\\Core\\Repository\\FieldType\\File', 'something' ) )
            throw new \Exception( "Array was not expected value" );
    }
}

class F
{
    public function __construct( array $config )
    {
        if ( !$config['b'] instanceof B )
            throw new \Exception( "b was not instance of 'B'" );
        if ( !$config['sub']['c'] instanceof C )
            throw new \Exception( "sub.c was not instance of 'C'" );
    }
}


class G
{
    public $hIntValue = null;
    public function __construct( Closure $lazyHServiceCall, $hIntValue, Closure $lazyHService )
    {
        $this->hIntValue = $lazyHServiceCall( $hIntValue );
        $service = $lazyHService();
        if ( !$service instanceof H )
            throw new \Exception( "\$lazyHService() did not return instance of 'H'" );
    }
}

class H
{
    public function __construct( $notUsedArgument = null )
    {
        if ( $notUsedArgument !== null )
            throw new \Exception( "\$notUsedArgument should be a vaue of null, got: " . $notUsedArgument );
    }

    public function timesTwo( $hIntValue )
    {
        return $hIntValue * 2;
    }
}


abstract class ExtendedTest
{
    public $test = null;
    public function __construct( H $h ){}
    public function setTest( $test )
    {
        if ( $test === null )
            throw new \Exception( "Got null as \$test" );

        $this->test = $test;
        return $this;
    }
}


class ExtendedTest1 extends ExtendedTest {}
class ExtendedTest2 extends ExtendedTest {}
class ExtendedTest3 extends ExtendedTest {}

class ExtendedTestCheck
{
    public $count;
    public function __construct( array $extendedTests )
    {
        if ( empty( $extendedTests ) )
            throw new \Exception( "Empty argument \$extendedTests" );

        $key = 0;
        foreach ( $extendedTests as $extendedTestName => $extendedTest )
        {
            $key++;
            if ( !$extendedTest instanceof ExtendedTest )
                throw new \Exception( "Values in \$extendedTests must extend ExtendedTest" );
            else if ( $extendedTestName !== "ExtendedTest{$key}" )
                throw new \Exception( "Keys had wrong value in \$extendedTests, got: $extendedTestName" );

        }
        $this->count = $key;
    }
}

class ExtendedTestLacyCheck
{
    public $count;
    public function __construct( array $extendedTests, $test = null )
    {
        if ( empty( $extendedTests ) )
            throw new \Exception( "Empty argument \$extendedTests" );

        $key = 0;
        foreach ( $extendedTests as $extendedTestName => $extendedTest )
        {
            $key++;
            if ( !is_callable( $extendedTest ) )
                throw new \Exception( "Values in \$extendedTests must be callable" );

            $extendedTest = $extendedTest( $test );
            if ( !$extendedTest instanceof ExtendedTest )
                throw new \Exception( "Values in \$extendedTests must extend ExtendedTest" );
            else if ( $extendedTestName !== "ExtendedTest{$key}" )
                throw new \Exception( "Keys had wrong value in \$extendedTests, got: $extendedTestName" );
            else if ( $extendedTest->test !== $test )
                throw new \Exception( "\$extendedTest->test is supposed to be '{$test}', got: {$extendedTest->test}" );

        }
        $this->count = $key;
    }
}
