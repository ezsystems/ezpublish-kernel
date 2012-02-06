<?php
/**
 * File containing the MediaFileHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Media\Handler as MediaHandler,
    eZ\Publish\Core\Repository\FieldType\Media\Type as MediaType,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository,
    PHPUnit_Framework_TestCase;

/**
 * Test case for {@link \eZ\Publish\Core\Repository\FieldType\Media\Handler}
 */
class MediaHandlerTest extends PHPUnit_Framework_TestCase
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
     * @var \eZ\Publish\Core\Repository\FieldType\Media\Handler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        BinaryRepository::setOverrideOptions( 'inmemory' );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new FileInfo( $this->mediaPath );
        $this->handler = new MediaHandler;
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Repository\FieldType\Media\Handler
     */
    public function testGetPluginsPageByType()
    {
        self::assertSame(
            MediaHandler::PLUGINSPAGE_FLASH,
            $this->handler->getPluginspageByType( MediaType::TYPE_FLASH )
        );
        self::assertSame(
            MediaHandler::PLUGINSPAGE_QUICKTIME,
            $this->handler->getPluginspageByType( MediaType::TYPE_QUICKTIME )
        );
        self::assertSame(
            MediaHandler::PLUGINSPAGE_REAL,
            $this->handler->getPluginspageByType( MediaType::TYPE_REALPLAYER )
        );
        self::assertSame(
            MediaHandler::PLUGINSPAGE_SILVERLIGHT,
            $this->handler->getPluginspageByType( MediaType::TYPE_SILVERLIGHT )
        );
        self::assertSame(
            MediaHandler::PLUGINSPAGE_WINDOWSMEDIA,
            $this->handler->getPluginspageByType( MediaType::TYPE_WINDOWSMEDIA )
        );
        self::assertSame(
            '',
            $this->handler->getPluginspageByType( MediaType::TYPE_HTML5_VIDEO )
        );
        self::assertSame(
            '',
            $this->handler->getPluginspageByType( MediaType::TYPE_HTML5_AUDIO )
        );
        self::assertSame(
            '',
            $this->handler->getPluginspageByType( 'UnknownMediaType' )
        );
    }
}
