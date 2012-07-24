<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation as APISubtreeLimitation;

/**
 * SubtreeLimitation is a Content Limitation & a Role Limitation
 */
class SubtreeLimitation extends APISubtreeLimitation
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
        if ( !$object instanceof Content )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content' );

        if ( $placement !== null  && !$placement instanceof Location )
            throw new InvalidArgumentException( '$placement', 'Must be of type: Location' );

        if ( empty( $this->limitationValues ) )
            return false;

        /**
         * Use $placement if provided, optionally used to check the specific location instead of all
         * e.g.: 'remove' in the context of removal of a specific location, or in case of 'create'
         *
         * @var \eZ\Publish\API\Repository\Values\Content\Location $placement
         */
        if ( $placement instanceof Location )
        {
            foreach ( $this->limitationValues as $limitationPathString )
            {
                if ( $placement->pathString === $limitationPathString )
                    return true;
                if ( strpos( $placement->pathString, $limitationPathString ) === 0 )
                    return true;
            }
            return false;
        }

        /**
         * Check all locations if no placement was provided
         *
         * @var \eZ\Publish\API\Repository\Values\Content\Content $object
         */
        $locations = $repository->getLocationService()->loadLocations( $object->contentInfo );
        foreach ( $locations as $location )
        {
            foreach ( $this->limitationValues as $limitationPathString )
            {
                if ( $location->pathString === $limitationPathString )
                    return true;
                if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                    return true;
            }
        }
        return false;
    }
}
