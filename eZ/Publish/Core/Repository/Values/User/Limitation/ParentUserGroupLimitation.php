<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation class.
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
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation as APIParentUserGroupLimitation;

/**
 * ParentUserGroupLimitation is a Content limitation
 */
class ParentUserGroupLimitation extends APIParentUserGroupLimitation
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
        if ( $this->limitationValues[0] != 1 )
        {
            throw new BadStateException(
                'Parent User Group limitation',
                'expected limitation value to be 1 but got:' . $this->limitationValues[0]
            );
        }

        if ( !$object instanceof Content )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content' );

        if ( $placement !== null  && !$placement instanceof Location )
            throw new InvalidArgumentException( '$placement', 'Must be of type: Location' );

        if ( $placement === null )
            return false;

         /**
          * @var \eZ\Publish\API\Repository\Values\Content\Location $placement
          */
        $parentContentInfo = $placement->getContentInfo();
        $currentUser = $repository->getCurrentUser();
        if ( $parentContentInfo->ownerId === $currentUser->id )
            return true;

        $userService = $repository->getUserService();
        $parentOwner = $userService->loadUser( $parentContentInfo->ownerId );
        $parentOwnerGroups = $userService->loadUserGroupsOfUser( $parentOwner );
        $currentUserGroups = $userService->loadUserGroupsOfUser( $currentUser );

        foreach ( $parentOwnerGroups as $parentOwnerGroup )
        {
            foreach ( $currentUserGroups as $currentUserGroup )
            {
                if ( $parentOwnerGroup->id === $currentUserGroup->id )
                    return true;
            }
        }

        return false;
    }
}
