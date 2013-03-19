<?php
/**
 * File contains: Abstract Base service test class for Mock testing
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
 * Base test case for tests on services using Mock testing
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
     * The Content / Location / ... handlers for the persistence handler mocks
     * @var \PHPUnit_Framework_MockObject_MockObject[] Key is relative to "\eZ\Publish\SPI\Persistence\"
     * @see getPersistenceMockHandler()
     */
    private $persistenceMockHandlers = array();

    /**
     * @var \eZ\Publish\SPI\IO\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $IOMock;

    /**
     * Get Real repository with mocked dependencies
     *
     * @param array $serviceSettings If set then non shared instance of Repository is returned
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository( array $serviceSettings = array() )
    {
        if ( $this->repository === null || !empty( $serviceSettings ) )
        {
            $repository = new Repository(
                $this->getPersistenceMock(),
                $serviceSettings,
                $this->getStubbedUser( 14 )
            );

            if ( !empty( $serviceSettings ) )
                return $repository;

            $this->repository = $repository;
        }
        return $this->repository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        if ( !isset( $this->repositoryMock ) )
        {
            $this->repositoryMock = self::getMock( "eZ\\Publish\\API\\Repository\\Repository" );
        }

        return $this->repositoryMock;
    }

    /**
     * Returns a persistence Handler mock
     *
     * @return \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceMock()
    {
        if ( !isset( $this->persistenceMock ) )
        {
            $this->persistenceMock = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\Handler",
                array(),
                array(),
                '',
                false
            );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'contentHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'searchHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Search\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'contentTypeHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Type\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'contentLanguageHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Language\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'locationHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Location\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'objectStateHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\ObjectState\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'trashHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Location\\Trash\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'userHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'User\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'sectionHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\Section\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'urlAliasHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' ) ) );

            $this->persistenceMock->expects( $this->any() )
                ->method( 'urlWildcardHandler' )
                ->will(  $this->returnValue( $this->getPersistenceMockHandler( 'Content\\UrlWildcard\\Handler' ) ) );
        }

        return $this->persistenceMock;
    }

    /**
     * Returns a persistence Handler mock
     *
     * @param string $handler For instance "Content\\Type\\Handler", must be relative to "eZ\Publish\SPI\Persistence"
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceMockHandler( $handler )
    {
        if ( !isset( $this->persistenceMockHandlers[$handler] ) )
        {
            $this->persistenceMockHandlers[$handler] = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\{$handler}",
                array(),
                array(),
                '',
                false
            );
        }

        return $this->persistenceMockHandlers[$handler];
    }

    /**
     * Returns User stub with $id as User/Content id
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser( $id )
    {
        return new User(
            array(
                'content' => new Content(
                    array(
                        'versionInfo' => new VersionInfo(
                            array(
                                'contentInfo' => new ContentInfo( array( 'id' => $id ) )
                            )
                        ),
                        'internalFields' => array()
                    )
                )
            )
        );
    }
}
