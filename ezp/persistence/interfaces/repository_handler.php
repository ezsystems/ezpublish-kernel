<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\persistence\Interfaces;

/**
 * The main handler for Storage Engine
 *
 * @package ezp
 * @subpackage persistence
 */
interface RepositoryHandler
{
    /**
     * @return \ezp\persistence\content\Interfaces\ContentHandler
     */
    public function contentHandler();

    /**
     * @return \ezp\persistence\content\type\Interfaces\Handler
     */
    public function contentTypeHandler();

    /**
     * @return \ezp\persistence\content\Interfaces\LocationHandler
     */
    public function locationHandler();

    /**
     * @return \ezp\persistence\user\Interfaces\UserHandler
     */
    public function userHandler();

    /**
     * @return \ezp\persistence\content\Interfaces\SectionHandler
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
