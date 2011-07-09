<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\Persistence\Interfaces;

/**
 * The main handler for Storage Engine
 *
 * @package ezp
 * @subpackage persistence
 */
interface RepositoryHandler
{
    /**
     * @return \ezp\Persistence\Content\Interfaces\ContentHandler
     */
    public function contentHandler();

    /**
     * @return \ezp\Persistence\Content\Type\Interfaces\Handler
     */
    public function contentTypeHandler();

    /**
     * @return \ezp\Persistence\Content\Interfaces\LocationHandler
     */
    public function locationHandler();

    /**
     * @return \ezp\Persistence\User\Interfaces\UserHandler
     */
    public function userHandler();

    /**
     * @return \ezp\Persistence\Content\Interfaces\SectionHandler
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
