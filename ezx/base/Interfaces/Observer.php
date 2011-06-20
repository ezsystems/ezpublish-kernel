<?php
/**
 * Interface for observer, extended with support for certain events.
 * $event = null means basically "updated" just as in normal observer code.
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */
namespace ezx\base\Interfaces;
interface Observer// extends \SplObserver
{
    /**
     * Called when subject has been updated
     *
     * @param Observable $subject
     * @param string|null $event
     * @return Observer
     */
    public function update( Observable $subject , $event  = null );
}