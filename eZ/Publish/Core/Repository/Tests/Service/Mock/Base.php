<?php

/**
 * File contains: Abstract Base service test class for Mock testing.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\User\User;

/**
 * Base test case for tests on services using Mock testing.
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * The Content / Location / Search ... handlers for the persistence / Search / .. handler mocks.
     *
     * @var \PHPUnit_Framework_MockObject_MockObject[] Key is relative to "\eZ\Publish\SPI\"
     *
     * @see getPersistenceMockHandler()
     */
    private $spiMockHandlers = array();

    /**
     * @var \eZ\Publish\SPI\IO\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $IOMock;

    /**
     * Get Real repository with mocked dependencies.
     *
     * @param array $serviceSettings If set then non shared instance of Repository is returned
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository(array $serviceSettings = array())
    {
        if ($this->repository === null || !empty($serviceSettings)) {
            $repository = new Repository(
                $this->getPersistenceMock(),
                $this->getSPIMockHandler('Search\\Handler'),
                $serviceSettings,
                $this->getStubbedUser(14)
            );

            if (!empty($serviceSettings)) {
                return $repository;
            }

            $this->repository = $repository;
        }

        return $this->repository;
    }

    protected $fieldTypeServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\FieldTypeService
     */
    protected function getFieldTypeServiceMock()
    {
        if (!isset($this->fieldTypeServiceMock)) {
            $this->fieldTypeServiceMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\FieldTypeService')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->fieldTypeServiceMock;
    }

    protected $fieldTypeRegistryMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\Helper\FieldTypeRegistry
     */
    protected function getFieldTypeRegistryMock()
    {
        if (!isset($this->fieldTypeRegistryMock)) {
            $this->fieldTypeRegistryMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\FieldTypeRegistry')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->fieldTypeRegistryMock;
    }

    protected $nameableFieldTypeRegistryMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\Helper\NameableFieldTypeRegistry
     */
    protected function getNameableFieldTypeRegistryMock()
    {
        if (!isset($this->nameableFieldTypeRegistryMock)) {
            $this->nameableFieldTypeRegistryMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\NameableFieldTypeRegistry')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->nameableFieldTypeRegistryMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        if (!isset($this->repositoryMock)) {
            $this->repositoryMock = self::getMock('eZ\\Publish\\API\\Repository\\Repository');
        }

        return $this->repositoryMock;
    }

    /**
     * Returns a persistence Handler mock.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceMock()
    {
        if (!isset($this->persistenceMock)) {
            $this->persistenceMock = $this->getMock(
                'eZ\\Publish\\SPI\\Persistence\\Handler',
                array(),
                array(),
                '',
                false
            );

            $this->persistenceMock->expects($this->any())
                ->method('contentHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('searchHandler')
                ->will($this->returnValue($this->getSPIMockHandler('Search\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('contentTypeHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Type\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('contentLanguageHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Language\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('locationHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Location\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('objectStateHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\ObjectState\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('trashHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Location\\Trash\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('userHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('User\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('sectionHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\Section\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('urlAliasHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\UrlAlias\\Handler')));

            $this->persistenceMock->expects($this->any())
                ->method('urlWildcardHandler')
                ->will($this->returnValue($this->getPersistenceMockHandler('Content\\UrlWildcard\\Handler')));
        }

        return $this->persistenceMock;
    }

    /**
     * Returns a SPI Handler mock.
     *
     * @param string $handler For instance "Content\\Type\\Handler" or "Search\\Handler", must be relative to "eZ\Publish\SPI"
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSPIMockHandler($handler)
    {
        if (!isset($this->spiMockHandlers[$handler])) {
            $this->spiMockHandlers[$handler] = $this->getMock(
                "eZ\\Publish\\SPI\\{$handler}",
                array(),
                array(),
                '',
                false
            );
        }

        return $this->spiMockHandlers[$handler];
    }

    /**
     * Returns a persistence Handler mock.
     *
     * @param string $handler For instance "Content\\Type\\Handler", must be relative to "eZ\Publish\SPI\Persistence"
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceMockHandler($handler)
    {
        return $this->getSPIMockHandler("Persistence\\{$handler}");
    }

    /**
     * Returns User stub with $id as User/Content id.
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser($id)
    {
        return new User(
            array(
                'content' => new Content(
                    array(
                        'versionInfo' => new VersionInfo(
                            array(
                                'contentInfo' => new ContentInfo(array('id' => $id)),
                            )
                        ),
                        'internalFields' => array(),
                    )
                ),
            )
        );
    }
}
