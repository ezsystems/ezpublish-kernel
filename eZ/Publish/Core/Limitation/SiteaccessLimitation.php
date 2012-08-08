<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation as APISiteaccessLimitation;

/**
 * SiteaccessLimitation is a User limitation
 */
class SiteaccessLimitation extends APISiteaccessLimitation
{
    /**
     * Evaluate permission against content and parent
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $placement In 'create' limitations; this is parent
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     * @return bool
     */
    public function evaluate( Repository $repository, ValueObject $object, ValueObject $placement = null )
    {
        if ( !$object instanceof User )
            throw new InvalidArgumentException( '$object', 'Must be of type: User' );

        if ( empty( $this->limitationValues ) )
            return false;

        /**
         * @var \eZ\Publish\API\Repository\Values\Content\Content $object
         * @todo Use current siteaccess as dependency in constructor, or define a way it can be injected in this function
         * 4.x limitationValues in default 64bit mode is: sprintf( '%u', crc32( $siteAccessName ) )
         */
        return false;
    }
}
