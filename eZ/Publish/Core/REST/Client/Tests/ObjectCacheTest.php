<?php
/**
 * File containing the ObjectCacheTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Client\ObjectCache;
use eZ\Publish\API\Repository\Values\ValueObject;

class ObjectCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testStoreRestore()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store( 'some-key', $object );

        $this->assertSame(
            $object,
            $cache->restore( 'some-key' )
        );
    }

    public function testStoreOverwrite()
    {
        $cache = $this->getCache();

        $firstObject = new TestValueObject();
        $secondObject = new TestValueObject();

        $cache->store( 'some-key', $firstObject );
        $cache->store( 'some-key', $secondObject );

        $this->assertSame(
            $secondObject,
            $cache->restore( 'some-key' )
        );
        $this->assertNotSame(
            $firstObject,
            $cache->restore( 'some-key' )
        );
    }

    public function testRestoreNotAvailable()
    {
        $cache = $this->getCache();

        $this->assertNull( $cache->restore( 'non-existent' ) );
    }

    public function testClear()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store( 'some-key', $object );
        $cache->clear( 'some-key' );

        $this->assertNull( $cache->restore( 'some-key' ) );
    }

    public function testClearAll()
    {
        $cache = $this->getCache();

        $object = new TestValueObject();

        $cache->store( 'some-key', $object );
        $cache->store( 'other-key', $object );

        $cache->clearAll();

        $this->assertNull( $cache->restore( 'some-key' ) );
        $this->assertNull( $cache->restore( 'other-key' ) );
    }

    protected function getCache()
    {
        return new ObjectCache();
    }
}
