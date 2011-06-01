<?php
/**
 * Interface for observer, extended with support for certain events.
 * $event = null means basically "updated" just as in normal observer code.
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine\model;
interface Interface_Observer// extends \SplObserver
{
    /**
     * Called when subject has been updated
     *
     * @param Interface_Observable $subject
     * @param string|null $event
     * @return Interface_Observer
     */
    public function update( Interface_Observable $subject , $event  = null );
}