<?php
/**
 * Interface for observables (subjects), extended with support for certain events.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
interface ObservableInterface// extends \SplSubject
{
    /**
     * Attach a event listener to this subject
     *
     * @param Observer $observer
     * @param string $event
     * @return Observable
     */
    public function attach( ObserverInterface $observer, $event = 'update' );

    /**
     * Detach a event listener to this subject
     *
     * @param Observer $observer
     * @param string $event
     * @return Observable
     */
    public function detach( ObserverInterface $observer, $event = 'update' );

    /**
     * Notify listeners about certain events, if $event is null then it's plain 'update'
     *
     * @param string $event
     * @return Observable
     */
    public function notify( $event = 'update' );
}