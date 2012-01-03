<?php
/**
 * Contains Interface for observables (subjects)
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;

/**
 * Interface for observables (subjects)
 *
 */
interface Observable// extends \SplSubject
{
    /**
     * Attach a event listener to this subject
     *
     * @param Observer $observer
     * @param string $event
     * @return Observable
     */
    public function attach( Observer $observer, $event = 'update' );

    /**
     * Detach a event listener to this subject
     *
     * @param Observer $observer
     * @param string $event
     * @return Observable
     */
    public function detach( Observer $observer, $event = 'update' );

    /**
     * Notify listeners about certain events, by default a 'update' event
     *
     * @param string $event
     * @param array|null $arguments
     * @return Observable
     */
    public function notify( $event = 'update', array $arguments = null );
}
