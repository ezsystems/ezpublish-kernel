<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache\Tests;

use eZ\Publish\Core\RequestCache\CachePool;
use stdClass;

/**
 * @covers \eZ\Publish\Core\RequestCache\CachePool
 * @todo test __construct()
 */
class CachePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testSet()
    {
        $cachePool = new CachePool();
        self::assertEquals( 0, $cachePool->count() );

        $value = new stdClass();
        self::assertSame( $value, $cachePool->set( 'test/0', $value ) );
        self::assertEquals( 1, $cachePool->count() );

        $value = 1;
        self::assertSame( $value, $cachePool->set( 'test/1', $value ) );
        self::assertEquals( 2, $cachePool->count() );

        $value = null;//remove
        self::assertSame( $value, $cachePool->set( 'test/1', $value ) );
        self::assertEquals( 1, $cachePool->count() );

        $value = array();
        self::assertSame( $value, $cachePool->set( 'test/2', $value ) );

        $value = array( 'answer' => 42 );
        self::assertSame( $value, $cachePool->set( 'test/3', $value ) );

        $value = "Testing";
        self::assertSame( $value, $cachePool->set( 'test/4', $value ) );
        self::assertEquals( 4, $cachePool->count() );

        return $cachePool;
    }

    /**
     * @depends testSet
     */
    public function testGet( CachePool $cachePool )
    {
        $value = new stdClass();
        self::assertEquals( $value, $cachePool->get( 'test/0' ) );

        $value = null;//removed
        self::assertEquals( $value, $cachePool->get( 'test/1' ) );

        $value = array();
        self::assertEquals( $value, $cachePool->get( 'test/2' ) );

        $value = array( 'answer' => 42 );
        self::assertEquals( $value, $cachePool->get( 'test/3' ) );

        $value = "Testing";
        self::assertEquals( $value, $cachePool->get( 'test/4' ) );

        return $cachePool;
    }

    /**
     * @depends testGet
     */
    public function testPurge( CachePool $cachePool )
    {
        $cachePool->purge();
        self::assertEquals( 0, $cachePool->count() );

        return $cachePool;
    }

    /**
     * @depends testPurge
     */
    public function testSetReachingLimit( CachePool $cachePool )
    {
        for ( $i = 0; $i < 150; $i++ )
        {
            $value = new stdClass();
            self::assertSame( $value, $cachePool->set( "test/{$i}", $value ) );
        }

        // CachePool reduces 30% each time limit is reached, so number should be between 70 and 100
        self::assertLessThanOrEqual( 100, $cachePool->count() );
        self::assertGreaterThanOrEqual( 70, $cachePool->count() );

        return $cachePool;
    }

    /**
     * @depends testSetReachingLimit
     */
    public function testRemove( CachePool $cachePool )
    {
        for ( $i = 0; $i < 150; $i++ )
        {
            $cachePool->remove( "test/{$i}" );
        }

        self::assertEquals( 0, $cachePool->count() );
    }
}

