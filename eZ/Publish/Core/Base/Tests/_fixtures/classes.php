<?php
/**
 * File contains: Tests classes
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;

use Closure;

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
        if ( $config['array'] !== array( 'ezfile' => 'eZ\\Publish\\Core\\FieldType\\File', 'something' ) )
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
            throw new \Exception( "\$notUsedArgument should be a value of null, got: " . $notUsedArgument );
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
            else if ( $key !== 3 && $extendedTestName !== "ExtendedTest{$key}" )
                throw new \Exception( "Keys had wrong value in \$extendedTests, got: $extendedTestName" );
            else if ( $key === 3 && $extendedTestName !== "ExtendedTest3:ExtendedTest2" )
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
            else if ( $key !== 3 && $extendedTestName !== "ExtendedTest{$key}" )
                throw new \Exception( "Keys had wrong value in \$extendedTests, got: $extendedTestName" );
            else if ( $key === 3 && $extendedTestName !== "ExtendedTest3:ExtendedTest2" )
                throw new \Exception( "Keys had wrong value in \$extendedTests, got: $extendedTestName" );
            else if ( $extendedTest->test !== $test )
                throw new \Exception( "\$extendedTest->test is supposed to be '{$test}', got: {$extendedTest->test}" );

        }
        $this->count = $key;
    }
}

class ParameterTest
{
    public $parameter;
    public function __construct( $parameter, $test = null )
    {
        if ( empty( $parameter ) )
            throw new \Exception( "Empty argument \$parameter" );

        if ( !empty( $test ) )
            throw new \Exception( "Argument should have been empty: \$test" );

        $this->parameter = $parameter;
    }
}
