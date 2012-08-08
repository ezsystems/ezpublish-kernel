<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation class.
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
use eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation as APIParentOwnerLimitation;

/**
 * ParentOwnerLimitation is a Content limitation
 */
class ParentOwnerLimitation extends APIParentOwnerLimitation
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
     *
     * @todo Add support for $limitationValues[0] == 2 when session values can be injected somehow
     */
    public function evaluate( Repository $repository, ValueObject $object, ValueObject $placement = null )
    {
        if ( $this->limitationValues[0] != 1 && $this->limitationValues[0] != 2 )
        {
            throw new BadStateException(
                'Parent Owner limitation',
                'expected limitation value to be 1 or 2 but got:' . $this->limitationValues[0]
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
        return $placement->getContentInfo()->ownerId === $repository->getCurrentUser()->id;
    }
}
