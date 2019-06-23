<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentTypeHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\UpdateHandler;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Content Type Handler.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $gatewayMock;

    /**
     * Content Updater mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdaterMock;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler\DoctrineDatabase::updateContentObjects
     */
    public function testUpdateContentObjects()
    {
        $handler = $this->getUpdateHandler();

        $updaterMock = $this->getContentUpdaterMock();

        $updaterMock->expects($this->once())
            ->method('determineActions')
            ->with(
                $this->isInstanceOf(
                    Type::class
                ),
                $this->isInstanceOf(
                    Type::class
                )
            )->will($this->returnValue([]));

        $updaterMock->expects($this->once())
            ->method('applyUpdates')
            ->with(
                $this->equalTo(23),
                $this->equalTo([])
            );

        $types = $this->getTypeFixtures();

        $handler->updateContentObjects($types['from'], $types['to']);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler\DoctrineDatabase::deleteOldType
     */
    public function testDeleteOldType()
    {
        $handler = $this->getUpdateHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('delete')
            ->with(
                $this->equalTo(23),
                $this->equalTo(0)
            );

        $types = $this->getTypeFixtures();

        $handler->deleteOldType($types['from'], $types['to']);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler\DoctrineDatabase::publishNewType
     */
    public function testPublishNewType()
    {
        $handler = $this->getUpdateHandler();

        $gatewayMock = $this->getGatewayMock();
        $updaterMock = $this->getContentUpdaterMock();

        $gatewayMock->expects($this->once())
            ->method('publishTypeAndFields')
            ->with($this->equalTo(23), $this->equalTo(1), $this->equalTo(0));

        $types = $this->getTypeFixtures();

        $handler->publishNewType($types['to'], 0);
    }

    /**
     * Returns an array with 'from' and 'to' types.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type[]
     */
    protected function getTypeFixtures()
    {
        $types = [];

        $types['from'] = new Type();
        $types['from']->id = 23;
        $types['from']->status = Type::STATUS_DEFINED;

        $types['to'] = new Type();
        $types['to']->id = 23;
        $types['to']->status = Type::STATUS_DRAFT;

        return $types;
    }

    /**
     * Returns the Update Handler to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler\DoctrineDatabase
     */
    protected function getUpdateHandler()
    {
        return new DoctrineDatabase(
            $this->getGatewayMock(),
            $this->getContentUpdaterMock()
        );
    }

    /**
     * Returns a gateway mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getGatewayMock()
    {
        if (!isset($this->gatewayMock)) {
            $this->gatewayMock = $this->getMockForAbstractClass(Gateway::class);
        }

        return $this->gatewayMock;
    }

    /**
     * Returns a Content Updater mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected function getContentUpdaterMock()
    {
        if (!isset($this->contentUpdaterMock)) {
            $this->contentUpdaterMock = $this->createMock(ContentUpdater::class);
        }

        return $this->contentUpdaterMock;
    }
}
