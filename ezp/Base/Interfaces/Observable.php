<?php
/**
 * Contains Interface for observables (subjects)
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Interfaces;

/**
 * Interface for observables (subjects)
 *
 * @package ezp
 * @subpackage base
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
     * Notify listeners about certain events, if $event is null then it's plain 'update'
     *
     * @param string $event
     * @return Observable
     */
    public function notify( $event = 'update' );
}