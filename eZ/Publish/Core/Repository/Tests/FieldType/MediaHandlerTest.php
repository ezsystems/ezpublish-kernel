<?php
/**
 * File containing the MediaHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\Media\Handler as MediaHandler,
    eZ\Publish\Core\FieldType\Media\Type as MediaType,
    splFileInfo,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,
    eZ\Publish\Core\Repository\Tests\FieldType;

/**
 * Test case for {@link \eZ\Publish\Core\FieldType\Media\Handler}
 */
class MediaHandlerTest extends FieldType
{
    /**
     * Path to test image
     * @var string
     */
    protected $mediaPath;

    /**
     * FileInfo object for test image
     * @var \splFileInfo
     */
    protected $mediaFileInfo;

    /**
     * Binary file handler object
     * @var \eZ\Publish\Core\FieldType\Media\Handler
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        $repository = new Repository(
            new InMemoryPersistenceHandler( $this->validatorService, $this->fieldTypeTools ),
            new InMemoryIOHandler()
        );
        $this->mediaPath = __DIR__ . '/developer-got-hurt.m4v';
        $this->mediaFileInfo = new splFileInfo( $this->mediaPath );
        $this->handler = new MediaHandler( $repository->getIOService() );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\FieldType\Media\Handler
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
