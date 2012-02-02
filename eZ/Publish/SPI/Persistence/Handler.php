<?php
/**
 * File containing the Persistence Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence;

/**
 * The main handler for Storage Engine
 *
 */
interface Handler
{
    /**
     * @return \ezp\Persistence\Content\Handler
     */
    public function contentHandler();

    /**
     * @return \ezp\Persistence\Content\Search\Handler
     */
    public function searchHandler();

    /**
     * @return \ezp\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler();

    /**
     * @return \ezp\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler();

    /**
     * @return \ezp\Persistence\Content\Location\Handler
     */
    public function locationHandler();

    /**
     * @return \ezp\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler();

    /**
     * @return \ezp\Persistence\User\Handler
     */
    public function userHandler();

    /**
     * @return \ezp\Persistence\Content\Section\Handler
     */
    public function sectionHandler();

    /**
     */
    public function beginTransaction();

    /**
     */
    public function commit();

    /**
     */
    public function rollback();
}
?>
