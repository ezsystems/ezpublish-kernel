<?php
/**
 * File containing the SectionHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content
 */

namespace ezp\persistence\content\Interfaces;

/**
 * @package ezp
 * @subpackage persistence_content
 */
interface SectionHandler
{
    /**
     * @param string $name
     * @param string $identifier
     * @return \ezp\persistence\content\Section
     */
    public function create( $name, $identifier );

    /**
     * @param int $id
     * @param string $name
     * @param string $identifier
     */
    public function update( $id, $name, $identifier );

    /**
     * @param int $id
     * @return \ezp\persistence\content\Section|null
     */
    public function load( $id );

    /**
     * @param int $id
     */
    public function delete( $id );

    /**
     * @param int $sectionId
     * @param int $contentId
     */
    public function assign( $sectionId, $contentId );
}
?>
