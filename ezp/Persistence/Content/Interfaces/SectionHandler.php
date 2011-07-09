<?php
/**
 * File containing the SectionHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_content
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * @package ezp
 * @subpackage persistence_content
 */
interface SectionHandler
{
    /**
     * @param string $name
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
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
     * @return \ezp\Persistence\Content\Section|null
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
