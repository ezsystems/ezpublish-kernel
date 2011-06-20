<?php
/**
 * Interface for observables (subjects), extended with support for certain events.
 * $event = null means basically "updated" just as in normal observable
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */
namespace ezx\base\Interfaces;
interface Observable// extends \SplSubject
{
    /**
     * Attach a event listener to this subject
     *
     * @param Observer $observer
     * @param string|null $event
     * @return Observable
     */
    public function attach( Observer $observer, $event = null );

    /**
     * Detach a event listener to this subject
     *
     * @param Observer $observer
     * @param string|null $event
     * @return Observable
     */
    public function detach( Observer $observer, $event = null );

    /**
     * Notify listeners about certain events, if $event is null then it's plain 'update'
     *
     * @param string|null $event
     * @return Observable
     */
    public function notify( $event = null );
}