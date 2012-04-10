<?php
/**
 * File containing the Visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output\Visitor;
use eZ\Publish\API\REST\Common\Output\Visitor;
use eZ\Publish\API\REST\Common\Message;
use eZ\Publish\API\REST\Client\Sessionable;

/**
 * Visitor for view models
 */
class TestSession extends Visitor implements Sessionable
{
    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     * @return void
     * @private
     */
    public function setSession( $id )
    {
        $this->setHeader( 'X-Test-Session', $id );
    }
}

