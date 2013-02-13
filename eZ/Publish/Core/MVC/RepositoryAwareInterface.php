<?php
/**
 * File containing the RepositoryAwareInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC;

use eZ\Publish\API\Repository\Repository;

interface RepositoryAwareInterface
{
    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return void
     */
    public function setRepository( Repository $repository );
}
