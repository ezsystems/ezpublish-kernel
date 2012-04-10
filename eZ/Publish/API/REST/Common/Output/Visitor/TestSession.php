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
use eZ\Publish\API\REST\Common\Sessionable;

/**
 * Visitor for view models
 */
class TestSession extends Visitor implements Sessionable
{
    /**
     * Session ID
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param string $id
     * @return void
     * @private
     */
    public function setSession( $id )
    {
        $this->sessionId = $id;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return string
     */
    public function visit( $data )
    {
        $message = parent::visit( $data );
        if ( $this->sessionId !== null )
        {
            $message->headers['X-Test-Session'] = $this->sessionId;
        }
        return $message;
    }
}

