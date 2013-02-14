<?php
/**
 * File contains: ServiceContainerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;

use eZ\Publish\Core\Base\ServiceContainer;
use PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ServiceContainerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . "/_fixtures/classes.php";
    }

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
        $b = $sc->get( 'BService' );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $b );
        self::assertFalse( $b->factoryExecuted );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     */
    public function testSimpleAliasService()
    {
        $sc = new ServiceContainer(
            array(
                'BService' => array(
                    'alias' => 'BServiceXHandler',
                ),
                'BServiceXHandler' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\B',
                )
            )
        );
        $b = $sc->get( 'BService' );
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
        $c = $sc->get( 'CService' );
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
        $a = $sc->get( 'AService' );
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
        $a = $sc->get( 'AService' );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\A', $a );
        self::assertEquals( '__', $a->string );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\B', $a->b );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Base\\Tests\\C', $a->c );
        self::assertEquals( 'BS', $a->c->string );
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
        $d = $sc->get( 'DService' );
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
                            'array' => array( 'ezfile' => 'eZ\\Publish\\Core\\FieldType\\File', 'something' ),
                        )
                    ),
                ),
            )
        );
        $obj = $sc->get( 'EService' );
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
        $obj = $sc->get( 'F' );
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
                        'lazyHServiceCall' => '%H:parent::timesTwo',
                        'hIntValue' => 42,
                        'lazyHService' => '%H:parent',
                    ),
                ),
                'H:parent' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\H',
                    'shared' => false,
                    'arguments' => array(),
                ),
                'parent' => array(
                    'arguments' => array( 'test' => 33 ),
                ),
            )
        );
        $obj = $sc->get( 'G' );
        self::assertEquals( 42 * 2, $obj->hIntValue );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::getListOfExtendedServices
     */
    public function testComplexExtendedServicesUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestCheck',
                    'arguments' => array(
                        'extendedTests' => '@:ExtendedTest',
                    ),
                ),
                'ExtendedTest1:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest3:ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H' ),
                ),
            ),
            array( '$H' => new H )
        );
        $obj = $sc->get( 'ExtendedTestCheck' );
        self::assertEquals( 3, $obj->count );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::getListOfExtendedServices
     */
    public function testComplexLazyExtendedServicesUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestLacyCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestLacyCheck',
                    'arguments' => array(
                        'extendedTests' => '%:ExtendedTest',
                    ),
                ),
                'ExtendedTest1:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest3:ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H' ),
                ),
            ),
            array( '$H' => new H )
        );
        $obj = $sc->get( 'ExtendedTestLacyCheck' );
        self::assertEquals( 3, $obj->count );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     * @covers \eZ\Publish\Core\Base\ServiceContainer::getListOfExtendedServices
     */
    public function testComplexLazyExtendedCallbackUsingHash()
    {
        $sc = new ServiceContainer(
            array(
                'ExtendedTestLacyCheck' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTestLacyCheck',
                    'arguments' => array(
                        'extendedTests' => '%:ExtendedTest::setTest',
                        'test' => 'newValue',
                    ),
                ),
                'ExtendedTest1:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest1',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest2',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest3:ExtendedTest2:ExtendedTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ExtendedTest3',
                    'arguments' => array( 'h' => '$H' ),
                ),
                'ExtendedTest' => array(
                    'arguments' => array( 'h' => '$H' ),
                ),
            ),
            array( '$H' => new H )
        );
        $obj = $sc->get( 'ExtendedTestLacyCheck' );
        self::assertEquals( 3, $obj->count );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ServiceContainer::__construct
     * @covers \eZ\Publish\Core\Base\ServiceContainer::get
     * @covers \eZ\Publish\Core\Base\ServiceContainer::lookupArguments
     */
    public function testParameters()
    {
        $testPath = "TestValue/Path";
        $sc = new ServiceContainer(
            array(
                'parameters' => array(
                    'storage_path' => $testPath
                ),
                'ParameterTest' => array(
                    'class' => 'eZ\\Publish\\Core\\Base\\Tests\\ParameterTest',
                    'arguments' => array(
                        'path' => '$storage_path',
                    ),
                ),
            )
        );
        $obj = $sc->get( 'ParameterTest' );
        self::assertEquals( $testPath, $obj->parameter );
    }
}
