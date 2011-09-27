<?php
/**
 * File containing the MediaFileHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Media\Handler as MediaFileHandler,
    ezp\Io\BinaryFile,
    ezp\Io\SysInfo,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository;

/**
 * Test case for {@link \ezp\Content\FieldType\Media\Handler}
 */
class MediaHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to test image
     * @var string
     */
    protected $mediaPath;

    /**
     * FileInfo object for test image
     * @var \ezp\Io\FileInfo
     */
    protected $mediaFileInfo;

    /**
     * Binary file handler object
     * @var \ezp\Content\FieldType\Media\Handler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
        $this->handler = new MediaFileHandler;
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \ezp\Content\FieldType\Media\Handler
     */
    public function testGetPluginsPageByType()
    {
        $this->markTestIncomplete();
    }
}
