<?php
/**
 * File containing the UserDomainTypeMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Helper\DomainTypeMapper;

use eZ\Publish\Core\FieldType\User\Value as UserValue;
use eZ\Publish\Core\Repository\Helper\DomainTypeMapper;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\User as SPIUser;
use eZ\Publish\SPI\Persistence\User\Handler as SPIUserHandler;

/**
 * DomainTypeMapper for User object
 *
 * @internal
 */
class UserDomainTypeMapper implements DomainTypeMapper
{
    /**
     * @var SPIUserHandler
     */
    protected $userHandler;

    /**
     * @param SPIUserHandler $userHandler
     */
    public function __construct( SPIUserHandler $userHandler )
    {
        $this->userHandler = $userHandler;
    }

    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param array $contentProperties Main properties for Content
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentObject( SPIContent $spiContent, array $contentProperties )
    {
        // Get spiUser value from Field Value
        foreach ( $contentProperties['internalFields'] as $field )
        {
            if ( $field->value instanceof UserValue )
            {
                $user = $field->value;
                break;
            }
        }

        $user = isset( $user ) ? $user : $this->userHandler->load( $spiContent->versionInfo->contentInfo->id );
        return new User(
            array(
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'hashAlgorithm' => (int)( $user instanceof SPIUser ? $user->hashAlgorithm : $user->passwordHashType ),
                'enabled' => ( $user instanceof SPIUser ? $user->isEnabled : $user->enabled ),
                'maxLogin' => (int)$user->maxLogin,
            ) + $contentProperties
        );
    }
}
