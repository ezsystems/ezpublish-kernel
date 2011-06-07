<?php
/**
 * Interface for observables (subjects), extended with support for certain events.
 * $event = null means basically "updated" just as in normal observable
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine;
interface Interface_Observable// extends \SplSubject
{
    /**
     * Attach a event listener to this subject
     *
     * @param Interface_Observer $observer
     * @param string|null $event
     * @return Interface_Observable
     */
    public function attach( Interface_Observer $observer, $event = null );

    /**
     * Detach a event listener to this subject
     *
     * @param Interface_Observer $observer
     * @param string|null $event
     * @return Interface_Observable
     */
    public function detach( Interface_Observer $observer, $event = null );

    /**
     * Notify listeners about certain events, if $event is null then it's plain 'update'
     *
     * @param string|null $event
     * @return Interface_Observable
     */
    public function notify( $event = null );
}