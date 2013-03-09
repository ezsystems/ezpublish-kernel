<?php
/**
 * File containing the FileInfoTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Tests\MimeTypeDetector;

use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo as MimeTypeDetector;

class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var MimeTypeDetector */
    protected $mimeTypeDetector;

    public function setUp()
    {
        $this->mimeTypeDetector = new MimeTypeDetector;
    }

    public function testGetFromPath()
    {
        self::assertEquals(
            $this->mimeTypeDetector->getFromPath( __FILE__ ),
            'text/x-php'
        );
    }

    public function testGetFromBuffer()
    {
        self::assertEquals(
            $this->mimeTypeDetector->getFromBuffer( file_get_contents( __FILE__ ) ),
            'text/x-php'
        );
    }
}
