<?php
/**
 * File containing the FileInfoTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests;
use ezp\Io\FileInfo,
    ReflectionObject;

class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    protected $imageInputPath;

    protected function setUp()
    {
        $this->imageInputPath = __DIR__ . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    /**
     * @group io
     * @covers \ezp\Io\FileInfo::getContentType
     */
    public function testContentType()
    {
        $fileInfo = new FileInfo( $this->imageInputPath );

        $ref = new ReflectionObject( $fileInfo );
        $refContentType = $ref->getProperty( 'contentType' );
        $refContentType->setAccessible( true );
        self::assertNull( $refContentType->getValue( $ref ), 'contentType object should be lazy loaded' );
        self::assertInstanceOf( 'ezp\\Io\\ContentType', $fileInfo->getContentType() );
    }
}
